<?php

namespace Cauditor\Tests;

use Cauditor\Api;
use Cauditor\Exception;
use PHPUnit_Framework_TestCase;

class ApiTest extends PHPUnit_Framework_TestCase
{
    protected $api = 'http://localhost:8000';

    /**
     * Make sure a local test server with documentroot tests\api is running,
     * e.g. like this: `php -S localhost:8000 -t tests/api`.
     */
    public function setUp()
    {
        if (!$this->isServerAvailable()) {
            $this->markTestSkipped('Local server is not running.');
        }
    }

    public function testGet()
    {
        $api = new Api($this->api);
        $result = $api->get('/get.php');
        $data = json_decode($result);

        $this->assertEquals('ok', $data->status);
    }

    public function testPut()
    {
        $api = new Api($this->api);
        $result = $api->put('/put.php', array('hello' => 'there'));
        $data = json_decode($result);

        $this->assertEquals('there', $data->hello);
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
