<?php
namespace InlineStyle;

use InlineStyle\Css\OrderedStyleSheet;
use InlineStyle\Html\ApplyStyleSheets;
use InlineStyle\Html\Document;
use InlineStyle\Html\ExtractStyleSheets;

/**
 * InlineStyleCompat
 * @author Christiaan Baartse <anotherhero@gmail.com>
 */
final class InlineStyleCompat
{
    /** @type Document */
    private $document;

    /**
     * @param string $html
     */
    public function __construct($html)
    {
        if (strlen($html) <= PHP_MAXPATHLEN && file_exists($html)) {
            $html = file_get_contents($html);
        }
        $this->document = new Document($html);
    }

    public function getHTML()
    {
        return (string) $this->document;
    }

    /**
     * @param string|array $string
     * @return InlineStyleCompat
     */
    public function applyStyleSheet($string)
    {
        $applyStylesheets = new ApplyStyleSheets(
            array_map(
                function ($string) {
                    return OrderedStyleSheet::fromString($string);
                },
                (array) $string
            )
        );

        $this->document = $this->document->applyTransform($applyStylesheets);

        return $this;
    }

    /**
     * @param string $selector
     * @param string $declaration
     * @return InlineStyleCompat
     */
    public function applyRule($selector, $declaration)
    {
        $this->applyStyleSheet($selector . '{' . $declaration . '}');

        return $this;
    }

    /**
     * @param null $context deprecated cannot be used anymore
     * @param string $basedir
     * @param array $devices
     * @return array
     */
    public function extractStylesheets($context, $basedir, $devices = array('all', 'screen', 'handheld'))
    {
        $extractStyleSheets = new ExtractStyleSheets(
            $basedir,
            $devices
        );

        $this->document = $this->document->applyTransform($extractStyleSheets);

        return array_map('strval', $extractStyleSheets->getStyleSheets());
    }
}
