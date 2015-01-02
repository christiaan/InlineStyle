<?php
namespace InlineStyle;

/**
 * InlineStyleTest
 */
class InlineStyleTest extends \PHPUnit_Framework_TestCase
{
    public function test_inline_without_any_styles()
    {
        $html = '<p>Hoi</p>';

        $actual = InlineStyle::inline($html);

        $this->assertEquals($html, $actual);
    }
}