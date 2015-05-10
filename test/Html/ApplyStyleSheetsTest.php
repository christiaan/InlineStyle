<?php
namespace InlineStyle\Html;

use InlineStyle\Css\OrderedStyleSheet;

class ApplyStyleSheetsTest extends \PHPUnit_Framework_TestCase
{
    public function test_apply_stylesheet()
    {
        $styleSheets = array(
            OrderedStyleSheet::fromString(
                file_get_contents(__DIR__ . '/external.css')
            ),
        );

        $apply = new ApplyStyleSheets($styleSheets);

        $transformed = $apply->transformDocument(
            file_get_contents(__DIR__ . '/extracted.html')
        );

        $this->assertStringEqualsFile(__DIR__ . '/applied.html', $transformed);
    }
}
