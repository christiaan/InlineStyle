<?php
namespace InlineStyle\Css;

class DeclarationsTest extends \PHPUnit_Framework_TestCase
{
    public function test_empty_styles()
    {
        $styles = Declarations::fromString('');

        $this->assertEquals('', (string) $styles);
    }

    public function test_minimizes_style()
    {
        $styles = Declarations::fromString('
      position: fixed;
      top: 0;
      right: 0;
      font-family:Georgia, Palatino, \'Palatino Linotype\', Times, \'Times New Roman\', serif;
      font-size: 16px;
      line-height: 1.5em;
');

        $this->assertEquals(
            'position:fixed;top:0;right:0;font-family:Georgia, Palatino, \'Palatino Linotype\', Times, \'Times New Roman\', serif;font-size:16px;line-height:1.5em',
            (string) $styles
        );
    }

    public function test_strips_unnecessary_whitespace_and_trailing_semicolons()
    {
        $this->assertEquals(
            'color:red',
            (string) Declarations::fromString('   color   :     red     ;;;')
        );
    }

    public function test_merge_styles()
    {
        $merged = Declarations::fromString('color:red')->merge(
            Declarations::fromString('width:200px')
        );

        $this->assertEquals(
            'color:red;width:200px',
            (string) $merged
        );
    }

    public function test_merge_styles_with_important_flags()
    {
        $merged = Declarations::fromString('color:red')->merge(
            Declarations::fromString('width:200px;color:orange !important')
        );

        $this->assertEquals(
            'color:orange !important;width:200px',
            (string) $merged
        );
    }
}
