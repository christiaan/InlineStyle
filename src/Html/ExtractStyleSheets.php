<?php
namespace InlineStyle\Html;

use InlineStyle\Css\OrderedStyleSheet;

/**
 * ExtractStyleSheets
 * @author Christiaan Baartse <anotherhero@gmail.com>
 */
final class ExtractStyleSheets implements Transform
{
    /** @type string */
    private $base;
    /** @type array */
    private $devices;
    /** @type OrderedStyleSheet[] */
    private $styleSheets;

    /**
     * @param string $base The base URI for relative stylesheets
     * @param array $devices
     */
    public function __construct($base, $devices = array('all', 'screen', 'handheld'))
    {
        $this->base = $base;
        $this->devices = $devices;
        $this->styleSheets = array();
    }

    /**
     * The extracted stylesheets that were retrieved during tranformation
     *
     * @return \InlineStyle\Css\OrderedStyleSheet[]
     */
    public function getStyleSheets()
    {
        return $this->styleSheets;
    }

    /**
     * Recursively extracts the stylesheet nodes from the DOMNode
     *
     * This cannot be done with XPath or a CSS selector because the order in
     * which the elements are found matters
     *
     * @param string $html
     * @return string New HTML for the document
     */
    public function transformDocument($html)
    {
        $dom = new \DOMDocument();
        $dom->formatOutput = true;

        // strip illegal XML UTF-8 chars
        // remove all control characters except CR, LF and tab
        $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $html); // 00-09, 11-31, 127
        $dom->loadHTML($html);

        $queue = array();
        $queue[] = $dom;

        $removeQueue = array();

        while ($node = array_shift($queue)) {
            // The DOMDocumentType extends the DOMNode but it's childNodes
            // property is NULL and not the documented DOMNodeList
            if ($node->childNodes) {
                foreach ($node->childNodes as $child) {
                    $queue[] = $child;
                }
            }

            if ($this->isStyleSheet($node) && $this->isForAllowedMediaDevice($node)) {
                $this->styleSheets[] = $this->extractStyleSheet($node);
                $removeQueue[] = $node;
            }
        }

        foreach ($removeQueue as $child) {
            $child->parentNode->removeChild($child);
        }

        return $dom->saveHTML();
    }

    private function isStyleSheet($child)
    {
        return $child instanceof \DOMElement &&
            ($this->isStyleNode($child) || $this->isLinkNode($child));
    }

    /**
     * @param \DOMElement $child
     * @return OrderedStyleSheet
     */
    private function extractStyleSheet(\DOMElement $child)
    {
        if ($this->isLinkNode($child)) {
            return $this->createExternal($child->getAttribute("href"));
        }

        return OrderedStyleSheet::fromString($child->nodeValue);
    }

    private function isStyleNode(\DOMElement $child)
    {
        return strtolower($child->nodeName) === 'style';
    }

    private function isLinkNode(\DOMElement $child)
    {
        return
            strtolower($child->nodeName) === 'link' &&
            strtolower($child->getAttribute('rel')) === 'stylesheet' &&
            $child->hasAttribute('href');
    }

    private function isForAllowedMediaDevice(\DOMElement $child)
    {
        $mediaAttribute = $child->getAttribute('media');
        $mediaAttribute = strtolower($mediaAttribute);
        $mediaDevices = explode(',', $mediaAttribute);
        $mediaDevices = array_map('trim', $mediaDevices);
        $mediaDevices = array_filter($mediaDevices);

        return empty($mediaDevices) ||
            count(array_intersect($this->devices, $mediaDevices)) > 0;
    }

    /**
     * @param string $href
     * @return OrderedStyleSheet
     */
    private function createExternal($href)
    {
        if ($this->base && false === strpos($href, "://")) {
            $href = "{$this->base}/{$href}";
        }
        $ext = @file_get_contents($href);
        return OrderedStyleSheet::fromString($ext);
    }
}
