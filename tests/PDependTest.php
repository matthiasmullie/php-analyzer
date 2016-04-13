<?php

namespace Cauditor\Tests;

use Cauditor\Analyzers\PDepend\Analyzer as PDependAnalyzer;
use Cauditor\Config;
use PHPUnit_Framework_TestCase;

class PDependTest extends PHPUnit_Framework_TestCase
{
    public function testAnalyzer()
    {
        $path = __DIR__.'/../vendor/matthiasmullie/php-skeleton';
        $config = __DIR__.'/analyze/php-skeleton.yml';
        $expect = file_get_contents(__DIR__.'/build/php-skeleton.json');

        $config = new Config($path, $config);
        $analyzer = new PDependAnalyzer($config);
        $metrics = $analyzer->execute();

        // generated JSON
        $this->assertEquals($expect, json_encode($metrics));
    }
}
