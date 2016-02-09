<?php

namespace Cauditor\Tests;

use Cauditor\XMLReader;
use PHPUnit_Framework_TestCase;

class XMLReaderTest extends PHPUnit_Framework_TestCase
{
    protected $reader;

    public function setUp()
    {
        parent::setUp();

        $this->reader = new XMLReader();
        $this->reader->open(__DIR__.'/xml/example.xml');
    }

    public function testName()
    {
        $result = $this->reader->readNext('child');
        $this->assertTrue($result);
        $this->assertEquals('child', $this->reader->localName);
        $this->assertEquals('first', $this->reader->getAttribute('attr'));

        $result = $this->reader->readNext('child');
        $this->assertTrue($result);
        $this->assertEquals('child', $this->reader->localName);
        $this->assertEquals('second', $this->reader->getAttribute('attr'));
    }

    public function testExclude()
    {
        // no find first child
        $result = $this->reader->readNext('child', 'parent');
        $this->assertTrue($result);
        $this->assertEquals('child', $this->reader->localName);
        $this->assertEquals('first', $this->reader->getAttribute('attr'));

        // can't find child because it stops at end of parent
        $result = $this->reader->readNext('child', 'parent');
        $this->assertFalse($result);

        // now find next child, inside next parent
        $result = $this->reader->readNext('child', 'parent');
        $this->assertTrue($result);
        $this->assertEquals('child', $this->reader->localName);
        $this->assertEquals('second', $this->reader->getAttribute('attr'));

        // can't find child because it stops at end of parent
        $result = $this->reader->readNext('child', 'parent');
        $this->assertFalse($result);

        // can't find any child anymore, end of document
        $result = $this->reader->readNext('child');
        $this->assertFalse($result);
    }
}
