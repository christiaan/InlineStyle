InlineStyle
===========
[![Build Status](https://secure.travis-ci.org/christiaan/InlineStyle.png)](http://travis-ci.org/christiaan/InlineStyle)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/christiaan/InlineStyle/badges/quality-score.png?s=f731e792fb2eaa305e294a1a2928e9bc96dca12b)](https://scrutinizer-ci.com/g/christiaan/InlineStyle/)

InlineStyle provides an easy way to apply embedded and external stylesheets
directly as inline styles on the HTML tags. This is especially targetted at mail
clients which mostly dont support stylesheets but do support the style attribute
for HTML tags.

Installation
------------
Run
    composer.phar require inlinestyle/inlinestyle
Or add the following to your composer.json file
	"require": {
		"inlinestyle/inlinestyle": "2.*"
	}

Usage
-----

The library has one simple exposed api the `InlineStyle::inline(string $html, array $options)` method.

```php
$inlinedHtml = InlineStyle::inline($html, [
  'formatOutput' => true,
  'charset' => 'utf8',
  'devices' => ['all', 'screen', 'handheld'],
  'baseUrl' => 'http://example.com'
]);
```

The options that can be passed to the inline method

| Name | default value | description |
|------------------------------------|
| formatOutput | true | Nicely formats output with indentation and extra space. |
| charset | utf8 | input character set, output is always utf8 |
| devices | ['all', 'screen', 'handheld'] | allowed media devices, styles for other devices are ignored |
| baseUrl | '' | relative links are relative to this url |
