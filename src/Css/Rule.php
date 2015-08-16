<?php
namespace InlineStyle\Css;

/**
 * Rule
 * @author Christiaan Baartse <anotherhero@gmail.com>
 */
final class Rule
{
    /** @type Selector */
    private $selector;
    /** @type Declarations */
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

    /**
     * @param Rule $b
     * @return bool
     */
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
        if (false === strpos($string, '{')) {
            return array();
        }
        list($selectors, $declarations) = explode('{', trim(trim($string), '}'), 2);
        $selectors = Selector::fromString($selectors);
        $declarations = Declarations::fromString($declarations);

        return array_map(function(Selector $selector) use($declarations) {
            return new Rule($selector, $declarations);
        }, $selectors);
    }
}
