<?php
/*
 * InlineStyle MIT License
 * 
 * Copyright (c) 2010 Christiaan Baartse
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
/**
 * Parses a html file and applies all embedded and external stylesheets inline
 * 
 * @author Christiaan Baartse <christiaan@baartse.nl>
 * @copyright 2010 Christiaan Baartse
 */
class InlineStyle
{
	/**
	 * @var DOMDocument the HTML as DOMDocument
	 */
	protected $_dom;
	
	/**
	 * @var CSSQuery instance to use css based selectors on our DOMDocument
	 */
	protected $_cssquery;
	
	/**
	 * Prepare all the necessary objects
	 * 
	 * @param string $html
	 */
	public function __construct($html, $encoding = 'UTF-8') {
		if(!class_exists("CSSQuery")) {
			throw new Exception(
				"InlineStyle needs the CSSQuery class");
		}
		
		$html = htmlspecialchars_decode(htmlentities((string) $html, ENT_NOQUOTES, $encoding), ENT_NOQUOTES);
		$this->_dom = new DOMDocument();
		if(file_exists($html)) {
			$this->_dom->loadHTMLFile($html);
		}
		else {
			$this->_dom->loadHTML($html);
		}
		$this->_cssquery = new CSSQuery($this->_dom);
	}
	
	/**
	 * Applies one or more stylesheets to the current document
	 * 
	 * @param string $stylesheet
	 * @return InlineStyle self
	 */
	public function applyStylesheet($stylesheet) {
		$stylesheet = (array) $stylesheet;
		foreach($stylesheet as $ss) {
			foreach($this->parseStylesheet($ss) as $arr) {
				list($selector, $style) = $arr;
				$this->applyRule($selector, $style);
			}
		}
		return $this;
	}
	
	/**
	 * Applies a style rule on the document
	 * @param string $selector
	 * @param string $style
	 * @return InlineStyle self
	 */
	public function applyRule($selector, $style) {
		$selector = trim(trim($selector), ",");
		if($selector) {
			$nodes = array();
			foreach(explode(",", $selector) as $sel) {
				if(false === stripos($sel, ":hover") &&
				false === stripos($sel, ":active") &&
				false === stripos($sel, ":visited")) {
					$nodes = array_merge($nodes, $this->_cssquery->query($sel));
				}
			}
			$style = $this->_styleToArray($style);
			foreach($nodes as $node) {
				$current = $node->hasAttribute("style") ?
					$this->_styleToArray($node->getAttribute("style")) :
					array();
				
				
				$current = $this->_mergeStyles($current, $style);
				$st = array();
				foreach($current as $prop => $val) {
					$st[] = "{$prop}:{$val}";
				}
				
				$node->setAttribute("style", implode(";", $st));
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
		return $this->_dom->saveHTML();
	}
	
	/**
	 * Recursively extracts the stylesheet nodes from the DOMNode
	 * @param DOMNode $node leave empty to extract from the whole document
	 * @return array the extracted stylesheets
	 */
	public function extractStylesheets(DOMNode $node = null, $base = "")
	{
		if(null === $node) {
			$node = $this->_dom;
		}
		$stylesheets = array();
		
		if(strtolower($node->nodeName) === "style") {
			$stylesheets[] = $node->nodeValue;
			$node->parentNode->removeChild($node);
		}
		else if(strtolower($node->nodeName) === "link") {
			if($node->hasAttribute("href")) {
				$href = $node->getAttribute("href");
				if($base && false === strpos($href, "://")) {
					$href = "{$base}/{$href}";
				}
				$ext = @file_get_contents($href);
				if($ext) {
					$stylesheets[] = $ext;
					$node->parentNode->removeChild($node);
				}
			}
		}
			
		if($node->hasChildNodes()) {
			foreach($node->childNodes as $child) {
				$stylesheets = array_merge($stylesheets,
					$this->extractStylesheets($child, $base));
			}
		}
		
		return $stylesheets;
	}
	
	/**
	 * Parses a stylesheet to selectors and properties
	 * @param string $stylesheet
	 * @return array
	 */
	public function parseStylesheet($stylesheet) {
		$parsed = array();
		$stylesheet = $this->_stripStylesheet($stylesheet);
		$stylesheet = trim(trim($stylesheet), "}");
		foreach(explode("}", $stylesheet) as $rule) {
			list($selector, $style) = explode("{", $rule, 2);
			$parsed[] = array(trim($selector), trim(trim($style), ";"));
		}
		
		return $parsed;
	}
	
	/**
	 * Parses style properties to a array which can be merged by mergeStyles()
	 * @param string $style
	 * @return array
	 */
	protected function _styleToArray($style) {
		$styles = array();
		$style = trim(trim($style), ";");
		if($style) {
			foreach(explode(";",$style) as $props) {
				$props = trim(trim($props), ";");
				preg_match('#^([-a-z0-9]+):(.*)$#i', $props, $matches);
			        list($match, $prop, $val) = $matches;
				$styles[$prop] = $val;
			}
		}
		return $styles;
	}
	
	/**
	 * Merges two sets of style properties taking !important into account
	 * @param array $styleA
	 * @param array $styleB
	 * @return array
	 */
	protected function _mergeStyles(array $styleA, array $styleB) {
		foreach($styleB as $prop => $val) {
			if(!isset($styleA[$prop]) ||
			substr(str_replace(" ", "", strtolower($styleA[$prop])), -10) !==
			"!important") {
					$styleA[$prop] = $val;
			}
		}
		return $styleA;
	}
	
	protected function _stripStylesheet($s)
	{
		$s = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','', $s);
		$s = str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '),'',$s);
		$s = str_replace('{ ', '{', $s);
		$s = str_replace(' }', '}', $s);
		$s = str_replace('; ', ';', $s);
		return $s;
	}
}
