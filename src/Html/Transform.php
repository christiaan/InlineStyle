<?php
namespace InlineStyle\Html;

/**
 * Transform
 * @author Christiaan Baartse <anotherhero@gmail.com>
 */
interface Transform
{
    /**
     * @param string $html
     * @return string New HTML for the document
     */
    public function transformDocument($html);
}