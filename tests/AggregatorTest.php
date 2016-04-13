<?php

namespace Cauditor\Tests;

use Cauditor\Aggregator;
use PHPUnit_Framework_TestCase;

class AggregatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Aggregator
     */
    protected $aggregator;

    public function setUp()
    {
        $metrics = file_get_contents(__DIR__.'/build/stub.json');
        $this->aggregator = new Aggregator(json_decode($metrics, true));
    }

    public function testAverage()
    {
        $avg = $this->aggregator->average();
        $this->assertEquals($avg['ccn'], 5);
    }

    public function testMin()
    {
        $min = $this->aggregator->min();
        $this->assertEquals($min['ccn'], 0);
    }

    public function testMax()
    {
        $max = $this->aggregator->max();
        $this->assertEquals($max['ccn'], 10);
    }

    public function testWeigh()
    {
        $weighed = $this->aggregator->weigh();
        $this->assertEquals($weighed['ccn'], 2);
    }
}
