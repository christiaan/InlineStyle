<?php
namespace InlineStyle\Css;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    public function test_is_more_specific_than()
    {
        $ruleA = new Rule(new Selector('#a'), Declarations::fromString(''));
        $ruleB = new Rule(new Selector('a'), Declarations::fromString(''));

        $this->assertTrue($ruleA->isMoreSpecificThan($ruleB));
    }
}
