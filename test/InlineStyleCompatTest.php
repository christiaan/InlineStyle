<?php

namespace InlineStyle\Tests;

use InlineStyle\InlineStyleCompat;

class InlineStyleCompatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @type \InlineStyle\InlineStyleCompat
     */
    private $object;
    private $basedir;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->basedir = __DIR__."/resources";
        $this->object = new InlineStyleCompat($this->basedir."/test.html");
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->object);
    }

    public function testGetHTML()
    {
    	$this->assertEquals(
           	file_get_contents($this->basedir."/testGetHTML.html"),
            $this->object->getHTML());
    }

    public function testApplyStyleSheet()
    {
        $this->object->applyStyleSheet("p:not(.p2) { color: red }");
        $this->assertEquals(
            file_get_contents($this->basedir."/testApplyStylesheet.html"),
            $this->object->getHTML());
    }

    public function testApplyRule()
    {
        $this->object->applyRule("p:not(.p2)", "color: red");
        $this->assertEquals(
            file_get_contents($this->basedir."/testApplyStylesheet.html"),
            $this->object->getHTML());
    }

    public function testExtractStylesheets()
    {
        $stylesheets = $this->object->extractStylesheets(null, $this->basedir);
        $this->assertEquals(
            include $this->basedir . "/testExtractStylesheets.php",
            $stylesheets
        );
    }

    public function testApplyExtractedStylesheet()
    {
        $stylesheets = $this->object->extractStylesheets(null, $this->basedir);
        $this->object->applyStylesheet($stylesheets);

        $this->assertEquals(
            file_get_contents($this->basedir."/testApplyExtractedStylesheet.html"),
            $this->object->getHTML());
    }

    public function testApplyStylesheetObeysSpecificity()
    {
        $this->object->applyStylesheet(<<<CSS
p {
    color: red;
}

.p2 {
    color: blue;
}

p.p2 {
    color: green;
}
CSS
);
        $this->assertEquals(
            file_get_contents($this->basedir."/testApplyStylesheetObeysSpecificity.html"),
            $this->object->getHTML());
    }

    public function testNonWorkingPseudoSelectors()
    {
        // Regressiontest for #5
        $this->object->applyStylesheet(<<<CSS
ul#nav li.active a:link, body.ie7 .col_3:visited h2 ~ h2 {
    color: blue;
}

ul > li ul li:active ol li:first-letter {
    color: red;
}
CSS
        );
    }

    /**
     * Regression tests for #10 _styleToArray crashes when presented with an invalid property name
     */
    public function testInvalidCssProperties()
    {
        $this->object->applyStylesheet(<<<CSS
ul {
    asohdtoairet;
    garbage: )&%)*(%);
}
CSS
);
    }

    public function testRegression24() {
        $content = '<p style="text-align:center;">Hello World!</p>';
        $htmldoc = new InlineStyleCompat($content);
        $htmldoc->applyStylesheet('p{
  text-align: left;
}');
        $expected = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><body><p style="text-align:center">Hello World!</p></body></html>

HTML;


        $this->assertEquals($expected, $htmldoc->getHTML());
        $htmldoc->applyStylesheet('p{
  text-align: left;
}');
        $this->assertEquals($expected, $htmldoc->getHTML());
    }

    public function testMultipleStylesheets28() {
        $htmldoc = new InlineStyleCompat(file_get_contents($this->basedir . '/testMultipleStylesheets.html'));
        $htmldoc->applyStylesheet($htmldoc->extractStylesheets(null, $this->basedir));
        $expected = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
<head><title>Example</title></head>
<body>
<p style='margin:0;padding:0 0 10px 0;background-image:url("someimage.jpg")'>Paragraph</p>
<strong style="font-weight:bold">Strong</strong>
<br>
</body>
</html>

HTML;
        $this->assertEquals($expected, $htmldoc->getHTML());
    }

    public function testMediaStylesheets31() {
        $htmldoc = new InlineStyleCompat(file_get_contents($this->basedir . '/testMediaStylesheets31.html'));
        $htmldoc->applyStylesheet($htmldoc->extractStylesheets(null, $this->basedir));
        $expected = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
<head><title>Example</title></head>
<body>
<style type="text/css" media="print">
    h1{
        display:none;
    }
</style>
<h1 style="color:yellow">An example title</h1>
<p style="line-height:1.5em;color:yellow !important">Paragraph 1</p>
</body>
</html>

HTML;
        $this->assertEquals($expected, $htmldoc->getHTML());
    }

    public function testLinkedMediaStylesheets31() {
        $htmldoc = new InlineStyleCompat(file_get_contents($this->basedir . '/testLinkedMediaStylesheets31.html'));
        $htmldoc->applyStylesheet($htmldoc->extractStylesheets(null, $this->basedir));
        $expected = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
<head>
<title>Example</title>
<link rel="stylesheet" href="external.css" media="print">
</head>
<body>
<h1>An example title</h1>
<p>Paragraph <strong style="font-weight:bold">1</strong></p>
</body>
</html>

HTML;
        $this->assertEquals($expected, $htmldoc->getHTML());
    }
}
