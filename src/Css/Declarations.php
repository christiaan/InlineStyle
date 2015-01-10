<?php
namespace InlineStyle\Css;

/**
 * Object representing the styles that get applied for a Selector
 *
 * @author Christiaan Baartse <anotherhero@gmail.com>
 */
final class Declarations
{
    private $styles;

    /**
     * @param array $styles
     */
    private function __construct(array $styles)
    {
        $this->styles = $styles;
    }

    /**
     * @param $string
     * @return Declarations
     */
    public static function fromString($string)
    {
        $styles = array();
        $style = trim($string, " \t\n\r;");
        $style = self::stripComments($style);
        foreach (explode(";", $style) as $props) {
            $props = trim(trim($props), ";");
            //Don't parse empty props
            if (!trim($props)) {
                continue;
            }
            //Only match valid CSS rules
            if (preg_match(
                    '/^([-a-z0-9\*]+)\s*:\s*(.*)$/i',
                    $props,
                    $matches
                ) &&
                isset($matches[0], $matches[1], $matches[2])
            ) {
                list(, $name, $value) = $matches;
                $styles[$name] = $value;
            }
        }

        return new Declarations($styles);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $st = array();
        foreach ($this->styles as $name => $value) {
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
        $styleA = $this->styles;
        $styleB = $other->styles;

        foreach ($styleB as $name => $val) {
            if (!isset($styleA[$name]) || $this->isImportant($styleA[$name])) {
                $styleA[$name] = $val;
            }
        }

        return new Declarations($styleA);
    }

    private static function stripComments($style)
    {
        return preg_replace('/\/\*[^*]*\*+([^\/][^*]*\*+)*\//', '', $style);
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