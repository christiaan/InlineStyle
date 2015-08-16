<?php
namespace InlineStyle\Css;

/**
 * UnsupportedLines
 * @author Christiaan Baartse <anotherhero@gmail.com>
 */
final class UnsupportedLines
{
    /**
     * Strip unsupported lines from the string
     *
     * @param string $string
     * @return string
     */
    public static function stripFromString($string)
    {
        // strip keyframes rules
        $string = preg_replace('/@[-|keyframes].*?\{.*?\}[ \r\n]*\}/s', '', $string);
        // strip comments
        return preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','', $string);
    }
}
