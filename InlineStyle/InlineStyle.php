<?php
namespace InlineStyle;

/*
 * InlineStyle MIT License
 *
 * Copyright (c) 2012 Christiaan Baartse
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

use Symfony\Component\CssSelector\Exception\ParseException;

/**
 * Parses a html file and applies all embedded and external stylesheets inline
 */
class InlineStyle
{
    /**
     * @var \DOMDocument the HTML as DOMDocument
     */
    private $_dom;

    /**
     * Prepare all the necessary objects
     *
     * @param string $html
     */
    public function __construct($html = '')
    {
        if ($html) {
            if ($html instanceof \DOMDocument) {
                $this->loadDomDocument(clone $html);
            } else if (strlen($html) <= PHP_MAXPATHLEN && file_exists($html)) {
                $this->loadHTMLFile($html);
            } else {
                $this->loadHTML($html);
            }
        }
    }

    /**
     * Load HTML file
     *
     * @param string $filename
     */
    public function loadHTMLFile($filename)
    {
        $this->loadHTML(file_get_contents($filename));
    }

    /**
     * Load HTML string (UTF-8 encoding assumed)
     *
     * @param string $html
     */
    public function loadHTML($html)
    {
        $dom = new \DOMDocument();
        $dom->formatOutput = true;

        // strip illegal XML UTF-8 chars
        // remove all control characters except CR, LF and tab
        $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $html); // 00-09, 11-31, 127

        $dom->loadHTML($html);
        $this->loadDomDocument($dom);
    }

    /**
     * Load the HTML as a DOMDocument directly
     *
     * @param \DOMDocument $domDocument
     */
    public function loadDomDocument(\DOMDocument $domDocument)
    {
        $this->_dom = $domDocument;
        foreach ($this->_getNodesForCssSelector('[style]') as $node) {
            $node->setAttribute(
                'inlinestyle-original-style',
                $node->getAttribute('style')
            );
        }
    }

    /**
     * Applies one or more stylesheets to the current document
     *
     * @param string $stylesheet
     * @return InlineStyle self
     */
    public function applyStylesheet($stylesheet)
    {
        $stylesheet = (array) $stylesheet;
        foreach($stylesheet as $ss) {
            $parsed = $this->parseStylesheet($ss);
            $parsed = $this->sortSelectorsOnSpecificity($parsed);
            foreach($parsed as $arr) {
                list($selector, $style) = $arr;
                $this->applyRule($selector, $style);
            }
        }

        return $this;
    }

    /**
     * @param string $sel Css Selector
     * @return array|\DOMNodeList|\DOMElement[]
     */
    private function _getNodesForCssSelector($sel)
    {
        try {
            if (class_exists('Symfony\Component\CssSelector\CssSelectorConverter')) {
                $converter = new \Symfony\Component\CssSelector\CssSelectorConverter(true);
                $xpathQuery = $converter->toXPath($sel);
            } else {
                $xpathQuery = \Symfony\Component\CssSelector\CssSelector::toXPath($sel);
            }

            return $this->_getDomXpath()->query($xpathQuery);
        }
        catch(ParseException $e) {
            // ignore css rule parse exceptions
        }

        return array();
    }

    /**
     * Applies a style rule on the document
     * @param string $selector
     * @param string $style
     * @return InlineStyle self
     */
    public function applyRule($selector, $style)
    {
        if($selector) {
            $nodes = $this->_getNodesForCssSelector($selector);
            $style = $this->_styleToArray($style);

            foreach($nodes as $node) {
                $current = $node->hasAttribute("style") ?
                    $this->_styleToArray($node->getAttribute("style")) :
                    array();

                $current = $this->_mergeStyles($current, $style);

                $node->setAttribute("style", $this->_arrayToStyle($current));
            }
        }

        return $this;
    }

    /**
     * Returns the DOMDocument as html
     *
     * @return string the HTML
     */
    public function getHTML()
    {
        $clone = clone $this;
        foreach ($clone->_getNodesForCssSelector('[inlinestyle-original-style]') as $node) {
            $current = $node->hasAttribute("style") ?
                $this->_styleToArray($node->getAttribute("style")) :
                array();
            $original = $node->hasAttribute("inlinestyle-original-style") ?
                $this->_styleToArray($node->getAttribute("inlinestyle-original-style")) :
                array();

            $current = $clone->_mergeStyles($current, $original);

            $node->setAttribute("style", $this->_arrayToStyle($current));
            $node->removeAttribute('inlinestyle-original-style');
        }

        return $clone->_dom->saveHTML();
    }

    /**
     * Recursively extracts the stylesheet nodes from the DOMNode
     *
     * This cannot be done with XPath or a CSS selector because the order in
     * which the elements are found matters
     *
     * @param \DOMNode $node leave empty to extract from the whole document
     * @param string $base The base URI for relative stylesheets
     * @param array $devices Considered devices
     * @param boolean $remove Should it remove the original stylesheets
     * @return array the extracted stylesheets
     */
    public function extractStylesheets($node = null, $base = '', $devices = array('all', 'screen', 'handheld'), $remove = true)
    {
        if(null === $node) {
            $node = $this->_dom;
        }

        $stylesheets = array();
        if($node->hasChildNodes()) {
            $removeQueue = array();
            /** @var $child \DOMElement */
            foreach($node->childNodes as $child) {
                $nodeName = strtolower($child->nodeName);
                if($nodeName === "style" && $this->isForAllowedMediaDevice($child->getAttribute('media'), $devices)) {
                    $stylesheets[] = $child->nodeValue;
                    $removeQueue[] = $child;
                } else if($nodeName === "link" && strtolower($child->getAttribute('rel')) === 'stylesheet' && $this->isForAllowedMediaDevice($child->getAttribute('media'), $devices)) {
                    if($child->hasAttribute("href")) {
                        $href = $child->getAttribute("href");

                        if($base && false === strpos($href, "://")) {
                            $href = "{$base}/{$href}";
                        }

                        $ext = @file_get_contents($href);

                        if($ext) {
                            $removeQueue[] = $child;
                            $stylesheets[] = $ext;
                        }
                    }
                } else {
                    $stylesheets = array_merge($stylesheets,
                        $this->extractStylesheets($child, $base, $devices, $remove));
                }
            }

            if ($remove) {
                foreach ($removeQueue as $child) {
                    $child->parentNode->removeChild($child);
                }
            }
        }

        return $stylesheets;
    }

    /**
     * Extracts the stylesheet nodes nodes specified by the xpath
     *
     * @param string $xpathQuery xpath query to the desired stylesheet
     * @return array the extracted stylesheets
     */
    public function extractStylesheetsWithXpath($xpathQuery)
    {
        $stylesheets = array();

        $nodes = $this->_getDomXpath()->query($xpathQuery);
        foreach ($nodes as $node)
        {
            $stylesheets[] = $node->nodeValue;
            $node->parentNode->removeChild($node);
        }

        return $stylesheets;
    }

    /**
     * Parses a stylesheet to selectors and properties
     * @param string $stylesheet
     * @return array
     */
    public function parseStylesheet($stylesheet)
    {
        $parsed = array();
        $stylesheet = $this->_stripStylesheet($stylesheet);
        $stylesheet = trim(trim($stylesheet), "}");
        foreach(explode("}", $stylesheet) as $rule) {
            //Don't parse empty rules
        	if(!trim($rule))continue;
        	list($selector, $style) = explode("{", $rule, 2);
            foreach (explode(',', $selector) as $sel) {
                $parsed[] = array(trim($sel), trim(trim($style), ";"));
            }
        }

        return $parsed;
    }

    public function sortSelectorsOnSpecificity($parsed)
    {
        usort($parsed, array($this, 'sortOnSpecificity'));
        return $parsed;
    }

    private function sortOnSpecificity($a, $b)
    {
        $a = $this->getScoreForSelector($a[0]);
        $b = $this->getScoreForSelector($b[0]);

        foreach (range(0, 2) as $i) {
            if ($a[$i] !== $b[$i]) {
                return $a[$i] < $b[$i] ? -1 : 1;
            }
        }
        return -1;
    }

    public function getScoreForSelector($selector)
    {
        return array(
            preg_match_all('/#\w/i', $selector, $result), // ID's
            preg_match_all('/\.\w/i', $selector, $result), // Classes
            preg_match_all('/^\w|\ \w|\(\w|\:[^not]/i', $selector, $result) // Tags
        );
    }

    /**
     * Parses style properties to a array which can be merged by mergeStyles()
     * @param string $style
     * @return array
     */
    private function _styleToArray($style)
    {
        $styles = array();
        $style = trim(trim($style), ";");
        if($style) {
            foreach(explode(";", $style) as $props) {
                $props = trim(trim($props), ";");
                //Don't parse empty props
                if(!trim($props))continue;
                //Only match valid CSS rules
                if (preg_match('#^([-a-z0-9\*]+):(.*)$#i', $props, $matches) && isset($matches[0], $matches[1], $matches[2])) {
                    list(, $prop, $val) = $matches;
                    $styles[$prop] = $val;
                }
            }
        }

        return $styles;
    }

    private function _arrayToStyle($array)
    {
        $st = array();
        foreach($array as $prop => $val) {
            $st[] = "{$prop}:{$val}";
        }
        return \implode(';', $st);
    }

    /**
     * Merges two sets of style properties taking !important into account
     * @param array $styleA
     * @param array $styleB
     * @return array
     */
    private function _mergeStyles(array $styleA, array $styleB)
    {
        foreach($styleB as $prop => $val) {
            if(!isset($styleA[$prop])
                || substr(str_replace(" ", "", strtolower($styleA[$prop])), -10) !== "!important")
            {
                $styleA[$prop] = $val;
            }
        }

        return $styleA;
    }

    private function _stripStylesheet($s)
    {
        // strip comments
        $s = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','', $s);
        
        // strip keyframes rules
        $s = preg_replace('/@[-|keyframes].*?\{.*?\}[ \r\n]*\}/s', '', $s);

        return $s;
    }

    private function _getDomXpath()
    {
        return new \DOMXPath($this->_dom);
    }

    public function __clone()
    {
        $this->_dom = clone $this->_dom;
    }

    private function isForAllowedMediaDevice($mediaAttribute, array $devices)
    {
        $mediaAttribute = strtolower($mediaAttribute);
        $mediaDevices = explode(',', $mediaAttribute);

        foreach ($mediaDevices as $device) {
            $device = trim($device);
            if (!$device || in_array($device, $devices)) {
                return true;
            }
        }
        return false;
    }
}
