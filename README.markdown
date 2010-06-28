InlineStyle
===========

InlineStyle provides an easy way to apply embedded and external stylesheets
directly as inline styles on the HTML tags. This is especially targetted at mail
clients which mostly dont support stylesheets but do support the style attribute
for HTML tags.

Usage
-----

First include both InlineStyle.php and CSSQuery.php.
Then create a new InlineStyle object from either a HTML string or HTML file.

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

License
-------

InlineStyle MIT License

Copyright (c) 2010 Christiaan Baartse

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.