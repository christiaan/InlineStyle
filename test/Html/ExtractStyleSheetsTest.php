<?php
namespace InlineStyle\Html;

class ExtractStyleSheetsTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformRemovesInlineStylesheet()
    {
        $html = file_get_contents(__DIR__ . '/testGetHTML.html');

        $transform = new ExtractStyleSheets(__DIR__);

        $html = $transform->transformDocument($html);

        $styleSheets = $transform->getStyleSheets();

        $this->assertCount(3, $styleSheets);
        $this->assertStringEqualsFile(__DIR__ . '/extracted.html', $html);
    }
}
