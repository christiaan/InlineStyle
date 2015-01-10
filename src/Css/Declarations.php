<?php
namespace InlineStyle\Css;

/**
 * Object representing the styles that get applied for a Selector
 *
 * @author Christiaan Baartse <anotherhero@gmail.com>
 */
final class Declarations
{
    private $declarations;

    /**
     * @param array $declarations
     */
    private function __construct(array $declarations)
    {
        $this->declarations = $declarations;
    }

    /**
     * @param $string
     * @return Declarations
     */
    public static function fromString($string)
    {
        $string = self::stripComments($string);
        $string = explode(";", $string);
        $string = array_filter(array_map('trim', $string));

        $declarations = array();
        foreach ($string as $declaration) {
            if (preg_match(
                    '/^([-a-z0-9\*]+)\s*:(.*)$/i',
                    $declaration,
                    $matches
                ) &&
                isset($matches[0], $matches[1], $matches[2])
            ) {
                list(, $name, $value) = $matches;
                $declarations[$name] = trim($value);
            }
        }

        return new Declarations($declarations);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $st = array();
        foreach ($this->declarations as $name => $value) {
            $st[] = $name . ':' . $value;
        }

        return implode(';', $st);
    }

    /**
     * Merge this object with another Declarations object into a new Declarations object
     *
     * @param Declarations $other
     * @return Declarations
     */
    public function merge(Declarations $other)
    {
        $styleA = $this->declarations;
        $styleB = $other->declarations;

        foreach ($styleB as $name => $val) {
            if (!isset($styleA[$name]) || $this->isImportant($styleA[$name])) {
                $styleA[$name] = $val;
            }
        }

        return new Declarations($styleA);
    }

    private static function stripComments($declaration)
    {
        return preg_replace('/\/\*[^*]*\*+([^\/][^*]*\*+)*\//', '', $declaration);
    }

    /**
     * Tells if a style value is marked with !important at the end
     *
     * @param string $value
     * @return bool
     */
    private function isImportant($value)
    {
        return substr(
            str_replace(" ", "", strtolower($value)),
            -10
        ) !== "!important";
    }
}