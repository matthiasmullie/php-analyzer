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

        $handle = fopen('php://temp', 'w+');

        $converter = new Converter($reader, $handle);
        $converter->convert();

        fseek($handle, 0);
        $expect = file_get_contents(__DIR__.'/build/cauditor.json');
        $this->assertEquals($expect, stream_get_contents($handle));

        fclose($handle);
    }
}
