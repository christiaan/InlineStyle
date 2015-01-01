<?php
namespace InlineStyle;

/**
 * InlineStyleTest
 */
class InlineStyleTest extends \PHPUnit_Framework_TestCase
{
    public function testCreationFromLocalFile()
    {
        $obj = InlineStyle::fromFile(__DIR__ . '/resources/test.html');

    }
}