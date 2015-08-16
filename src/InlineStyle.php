<?php
namespace InlineStyle;

/*
 * InlineStyle MIT License
 *
 * Copyright (c) 2015 Christiaan Baartse
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
use InlineStyle\Html\ApplyStyleSheets;
use InlineStyle\Html\Document;
use InlineStyle\Html\ExtractStyleSheets;

/**
 * Main entry class to use the InlineStyle library
 * @api
 */
final class InlineStyle
{
    /**
     * @param string $html
     * @param array $options
     * @return string
     */
    public static function inline($html, array $options = array())
    {
        $document = new Document($html);

        $options = self::defaultOptions($options);

        $extractStyleSheets = new ExtractStyleSheets(
            $options['baseUrl'],
            $options['devices']
        );

        $document = $document->applyTransform($extractStyleSheets);

        $applyStyleSheets = new ApplyStyleSheets(
            $extractStyleSheets->getStyleSheets()
        );

        $document = $document->applyTransform($applyStyleSheets);

        return (string) $document;
    }

    /**
     * @param array $options
     * @return array
     */
    private static function defaultOptions(array $options)
    {
        $defaultOptions = array(
            'baseUrl' => '',
            'devices' => array('all', 'screen', 'handheld')
        );
        return array_replace($defaultOptions, $options);
    }
}
