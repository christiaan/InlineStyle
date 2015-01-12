<?php
namespace InlineStyle\Css;

/**
 * Comment
 * @author Christiaan Baartse <anotherhero@gmail.com>
 */
final class Comment
{
    public static function stripFromString($string)
    {
        return preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','', $string);
    }
}