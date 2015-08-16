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
    /** @var Document */
    private $document;

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

    public function applyRule($selector, $declaration)
    {
        $this->applyStyleSheet($selector . '{' . $declaration . '}');

        return $this;
    }

    public function extractStylesheets(Document $document = null, $basedir, $devices = array('all', 'screen', 'handheld'))
    {
        $extractStyleSheets = new ExtractStyleSheets(
            $basedir,
            $devices
        );

        $this->document = $this->document->applyTransform($extractStyleSheets);

        return array_map('strval', $extractStyleSheets->getStyleSheets());
    }
}
