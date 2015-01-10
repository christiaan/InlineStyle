<?php
namespace InlineStyle\Css;

/**
 * Selector
 * @author Christiaan Baartse <anotherhero@gmail.com>
 */
final class Selector
{
    private $selector;

    function __construct($selector)
    {
        if (strpos($selector, ',') !== false) {
            throw new \InvalidArgumentException('Selector contains a , which is not allowed');
        }
        $this->selector = (string) $selector;
    }

    function __toString()
    {
        return $this->selector;
    }

    /**
     * @param Selector $other
     * @return bool
     */
    public function isMoreSpecificThan(Selector $other)
    {
        $score = $this->getScore();
        $otherScore = $other->getScore();

        foreach (array('ids', 'classes', 'tags') as $key) {
            if ($score[$key] !== $otherScore[$key]) {
                return $score[$key] > $otherScore[$key];
            }
        }

        return false;
    }

    /**
     * @return array
     */
    private function getScore()
    {
        return array(
            'ids' => preg_match_all('/#\w/i', $this->selector),
            'classes' => preg_match_all('/\.\w/i', $this->selector),
            'tags' =>preg_match_all('/^\w|\ \w|\(\w|\:[^not]/i', $this->selector)
        );
    }
}