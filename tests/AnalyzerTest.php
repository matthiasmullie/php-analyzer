<?php

namespace Cauditor\Tests;

use Cauditor\Analyzer;
use Cauditor\Config;
use PHPUnit_Framework_TestCase;

class AnalyzerTest extends PHPUnit_Framework_TestCase
{
    protected $reader;

    /**
     * One of the things being tested here is the API call, which is sent to a
     * test file. Make sure a local test server with documentroot tests\analyze
     * is running, e.g. like this:
     * `php -S localhost:8000 -t tests/analyze`.
     *
     * @throws \Cauditor\Exception
     */
    public function testAnalyzer()
    {
        $path = __DIR__.'/../vendor/matthiasmullie/php-skeleton';
        $config = __DIR__.'/analyze/php-skeleton.yml';

        $config = new Config($path, $config);
        $analyzer = new Analyzer($config);
        $result = $analyzer->run('http://localhost:8000/api.php');

        $expect = file_get_contents(__DIR__.'/build/cauditor.json');
        // generated JSON file
        $this->assertEquals($expect, file_get_contents(__DIR__.'/analyze/tmp/cauditor.json'));
        // submitted to API
        $this->assertEquals($expect, $result);
    }
}
