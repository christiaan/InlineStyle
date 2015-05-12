<?php
namespace InlineStyle;

/**
 * InlineStyleTest
 */
class InlineStyleTest extends \PHPUnit_Framework_TestCase
{
    private $basedir;
    private $document;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->basedir = __DIR__ . "/resources";
        $this->document = $this->basedir . "/test.html";
    }

    public function testApplyStyleSheet()
    {
        $this->object->applyStyleSheet("p:not(.p2) { color: red }");
        $this->assertEquals(
            file_get_contents($this->basedir."/testApplyStylesheet.html"),
            $this->object->getHTML());
    }
}