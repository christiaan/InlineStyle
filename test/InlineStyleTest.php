<?php
namespace InlineStyle;

class InlineStyleTest extends \PHPUnit_Framework_TestCase
{
    public function test_basic_page()
    {
        $html = InlineStyle::inline(
            file_get_contents(__DIR__ . '/resources/test.html'),
            array('baseUrl' => __DIR__ . '/resources')
        );

        $this->assertEquals(
            file_get_contents(__DIR__."/resources/testApplyExtractedStylesheet.html"),
            $html
        );
    }

    public function testIllegalXmlUtf8Chars()
    {
        // check an exception is not thrown when loading up illegal XML UTF8 chars
        $html = InlineStyle::inline(
            "<html><body>".chr(2).chr(3).chr(4).chr(5)."</body></html>"
        );

        $this->assertEquals(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><body></body></html>
',
            $html
        );
    }
}
