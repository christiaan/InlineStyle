<?php
namespace InlineStyle\Css;

class SelectorTest extends \PHPUnit_Framework_TestCase
{
    public function test_selector_stays_the_same()
    {
        $this->assertEquals(
            'header img#header[href]',
            (string) new Selector('header img#header[href]')
        );
    }

    /**
     * @dataProvider provideSelectors
     * @param Selector $a
     * @param Selector $b
     */
    public function test_is_more_specific_than(Selector $a, Selector $b)
    {
        $this->assertTrue(
            $a->isMoreSpecificThan($b),
            $a . ' should be more specific then ' . $b
        );
    }

    public function provideSelectors()
    {
        return array(
            array(
                new Selector('#id'),
                new Selector('.class')
            ),
            array(
                new Selector('.class'),
                new Selector('tag')
            ),
            array(
                new Selector('#id'),
                new Selector('tag')
            ),
            array(
                new Selector('p.p2'),
                new Selector('p')
            ),
        );
    }
}
