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

    public function __toString()
    {
        return (string) $this->selector . '{' . $this->declarations . '}';
    }

    /**
     * @return Selector
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * @return Declarations
     */
    public function getDeclarations()
    {
        return $this->declarations;
    }

    /**
     * Parse the rules out of the string
     *
     * @param string $string
     * @return Rule[]
     */
    public static function fromString($string)
    {
        $rules = array();
        if (false === strpos($string, '{')) {
            return $rules;
        }
        list($selectors, $declarations) = explode('{', trim(trim($string), '}'), 2);
        $selectors = Selector::fromString($selectors);
        $declarations = Declarations::fromString($declarations);

        foreach ($selectors as $selector) {
            $rules[] = new Rule(
                $selector,
                $declarations
            );
        }
        return $rules;
    }
}