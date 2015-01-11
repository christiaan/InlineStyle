<?php
namespace InlineStyle\Css;

/**
 * Rule
 * @author Christiaan Baartse <anotherhero@gmail.com>
 */
final class Rule
{
    private $selector;
    private $declarations;

    /**
     * @param Selector $selector
     * @param Declarations $declarations
     */
    public function __construct(Selector $selector, Declarations $declarations)
    {
        $this->selector = $selector;
        $this->declarations = $declarations;
    }

    public function isMoreSpecificThan(Rule $b)
    {
        return $this->selector->isMoreSpecificThan($b->selector);
    }
}