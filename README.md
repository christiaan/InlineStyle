InlineStyle
===========

InlineStyle provides an easy way to apply embedded and external stylesheets
directly as inline styles on the HTML tags. This is especially targetted at mail
clients which mostly dont support stylesheets but do support the style attribute
for HTML tags.

Usage
-----

Use composer to download required dependencies.

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
