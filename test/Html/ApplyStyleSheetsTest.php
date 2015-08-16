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

    public function test_last_rule_wins()
    {
        $styleSheets = array(
            OrderedStyleSheet::fromString(
                'p {
    color: red;
}

.p2 {
    color: blue;
}

p.p2 {
    color: green;
}'
            ),
        );

        $apply = new ApplyStyleSheets($styleSheets);

        $transformed = $apply->transformDocument(
            file_get_contents(__DIR__ . '/extracted.html')
        );

        $this->assertStringEqualsFile(__DIR__ . '/applied_specificity.html', $transformed);
    }
}
