<?php
namespace InlineStyle\Html;

/**
 * Document
 * @author Christiaan Baartse <anotherhero@gmail.com>
 */
final class Document
{
    private $html;

    /**
     * @param string $html
     */
    public function __construct($html)
    {
        $this->html = $html;
    }

    public function __toString()
    {
        return $this->html;
    }

    /**
     * @param Transform $transform
     * @return Document The transformed document
     */
    public function applyTransform(Transform $transform)
    {
        return new Document($transform->transformDocument($this->html));
    }
}