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

    public function test_rule_is_parsed()
    {
        $rules = Rule::fromString('a, img { color: red }');

        $this->assertCount(2, $rules);

        $this->assertEquals('a{color:red}', (string) $rules[0]);
        $this->assertEquals('img{color:red}', (string) $rules[1]);
    }
}
