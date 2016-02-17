<?php

namespace Cauditor\Tests;

use Cauditor\Analyzer;
use Cauditor\Config;
use Cauditor\Exception;
use PHPUnit_Framework_TestCase;

class AnalyzerTest extends PHPUnit_Framework_TestCase
{
    protected $api = 'http://localhost:8000';

    protected $reader;

    /**
     * One of the things being tested here is the API call, which is sent to a
     * test file. Make sure a local test server with documentroot tests\analyze
     * is running, e.g. like this:
     * `php -S localhost:8000 -t tests/analyze`.
     *
     * @throws Exception
     */
    public function testAnalyzer()
    {
        if (!$this->isServerAvailable()) {
            $this->markTestSkipped('Local server is not running.');
        }

        $path = __DIR__.'/../vendor/matthiasmullie/php-skeleton';
        $config = __DIR__.'/analyze/php-skeleton.yml';
        $expect = file_get_contents(__DIR__.'/build/cauditor.json');

        $config = new Config($path, $config);
        $analyzer = new Analyzer($config);
        $result = $analyzer->run($this->api.'/api.php');

        // generated JSON file
        $this->assertEquals($expect, file_get_contents(__DIR__.'/analyze/tmp/cauditor.json'));

        // submitted to API
        $result = (array) json_decode($result);
        $this->assertEquals($expect, $result['json']);

        /*
         * We're generating metrics for a different project than this one, but
         * for build data we can't: this is the project we're building, and this
         * is the project we'll get data for... We can't expect this to be
         * php-skeleton, so let's see if it correctly recognized this project.
         */
        $this->assertEquals('cauditor/php-analyzer', $result['slug']);
    }

    /**
     * @return bool
     */
    protected function isServerAvailable()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->api);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result !== false;
    }
}
