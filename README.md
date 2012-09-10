InlineStyle
===========
[![Build Status](https://secure.travis-ci.org/christiaan/InlineStyle.png)](http://travis-ci.org/christiaan/InlineStyle)

InlineStyle provides an easy way to apply embedded and external stylesheets
directly as inline styles on the HTML tags. This is especially targetted at mail
clients which mostly dont support stylesheets but do support the style attribute
for HTML tags.

Installation
------------
Run
    composer.phar require inlinestyle/inlinestyle
Or add the following to your composer.json file
	"require": {
		"inlinestyle/inlinestyle": "1.0"
	}

Usage
-----

Use composer to download required dependencies.

Import InlineStyle

    use \InlineStyle\InlineStyle;

Create a new InlineStyle object from either a HTML string or HTML file.

    $htmldoc = new InlineStyle("testfiles/test.html");

or

    $htmldoc = new InlineStyle(file_get_contents("http://github.com"));

### Apply the embedded and external stylesheets

First we'll have to extract the stylesheets from the document and then we have
to apply them.

    $htmldoc->applyStylesheet($htmldoc->extractStylesheets());

The second param is the base url that is used to parse the links to external
stylesheets.

    $htmldoc->applyStylesheet($htmldoc->extractStylesheets(null, "http://github.com"));

### Applying additional stylesheets

This class can also be used to apply a given css template to each processed HTML
file.

    $htmldoc->applyStylesheet(file_get_contents("testfiles/external.css"));

### Retrieving the modified HTML

After calling applyStylesheet various times the resulting HTML can be retrieved as a string using getHTML.

    $html = $htmldoc->getHTML();
