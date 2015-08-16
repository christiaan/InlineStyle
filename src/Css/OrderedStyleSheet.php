<?php
namespace InlineStyle\Css;

/**
 * OrderedStyleSheet
 * @author Christiaan Baartse <anotherhero@gmail.com>
 */
final class OrderedStyleSheet
{
    /** @var Rule[] */
    private $rules;

    /**
     * @param Rule[] $rules
     */
    function __construct(array $rules)
    {
        usort($rules, function(Rule $a, Rule $b) {
            return $b->isMoreSpecificThan($a) ? -1 : 1;
        });
        $this->rules = $rules;
    }

    public static function fromString($string)
    {
        $string = UnsupportedLines::stripFromString($string);

        $rules = array();
        foreach (explode('}', $string) as $rule) {
            $rules = array_merge($rules, Rule::fromString($rule . '}'));
        }

        return new OrderedStyleSheet($rules);
    }

    public function __toString()
    {
        return implode("\n", array_map('strval', $this->rules));
    }

    public function merge(OrderedStyleSheet $other)
    {
        return new OrderedStyleSheet(
            array_merge($this->rules, $other->rules)
        );
    }

    /**
     * @return array|Rule[]
     */
    public function getRules()
    {
        return $this->rules;
    }
}
