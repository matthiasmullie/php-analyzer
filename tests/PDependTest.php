<?php

namespace Cauditor\Tests;

use Cauditor\Analyzers\PDepend;
use Cauditor\Config;
use PHPUnit_Framework_TestCase;

class PDependTest extends PHPUnit_Framework_TestCase
{
    public function testAnalyzer()
    {
        $path = __DIR__.'/../vendor/matthiasmullie/php-skeleton';
        $config = __DIR__.'/analyze/php-skeleton.yml';
        $expect = file_get_contents(__DIR__.'/build/cauditor.json');

        $config = new Config($path, $config);
        $analyzer = new PDepend($config);
        $metrics = $analyzer->execute();

        // generated JSON
        $this->assertEquals($expect, json_encode($metrics));
    }
}
