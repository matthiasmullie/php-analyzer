<?php

namespace Cauditor\Tests;

use Cauditor\Converter;
use Cauditor\XMLReader;
use PHPUnit_Framework_TestCase;

class ConverterTest extends PHPUnit_Framework_TestCase
{
    protected $reader;

    public function testConversion()
    {
        $reader = new XMLReader();
        $reader->open(__DIR__.'/build/pdepend.xml');

        $converter = new Converter($reader);
        $result = $converter->convert($reader);

        $expect = file_get_contents(__DIR__.'/build/cauditor.json');
        $this->assertEquals($expect, $result);
    }
}
