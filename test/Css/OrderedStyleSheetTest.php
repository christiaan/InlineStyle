<?php
namespace InlineStyle\Css;

class OrderedStyleSheetTest extends \PHPUnit_Framework_TestCase
{
    public function test_parse_simple_stylesheet()
    {
        $string = <<<CSS
a[href], #id, #id.class, .class { font-family: "Arvo", Courier, monospace;
/* set color */
color: #003399;}
CSS;

        $stylesheet = OrderedStyleSheet::fromString($string);

        $this->assertEquals(
            <<<CSS
a[href]{font-family:"Arvo", Courier, monospace;color:#003399}
.class{font-family:"Arvo", Courier, monospace;color:#003399}
#id{font-family:"Arvo", Courier, monospace;color:#003399}
#id.class{font-family:"Arvo", Courier, monospace;color:#003399}

CSS
            ,
            (string) $stylesheet

        );
    }
}
