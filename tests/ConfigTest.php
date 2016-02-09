<?php

namespace Cauditor\Tests;

use Cauditor\Config;
use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    protected $path;

    public function setUp()
    {
        parent::setUp();

        $this->path = realpath(__DIR__.'/..');
    }

    public function testNoFile()
    {
        $config = new Config($this->path);

        // confirm that default value is used when no config is specified
        $this->assertEquals($this->path.'/build/cauditor', $config['build_path']);
    }

    public function testInvalidFile()
    {
        $config = new Config($this->path, __DIR__.'/config/non-existing.yml');

        // confirm that default value is used when invalid config is specified
        $this->assertEquals($this->path.'/build/cauditor', $config['build_path']);
    }

    public function testOverrideFile()
    {
        $config = new Config($this->path, __DIR__.'/config/override.yml');

        // confirm that config-specified value is used
        $this->assertEquals($this->path.'/custom/build/path', $config['build_path']);
    }

    public function testEmptyFile()
    {
        $config = new Config($this->path, __DIR__.'/config/empty.yml');

        // confirm that default value is used for what's not in config
        $this->assertEquals($this->path.'/build/cauditor', $config['build_path']);
    }

    public function testEnforceExcludeFolders()
    {
        $config = new Config($this->path, __DIR__.'/config/override.yml');

        // confirm that some folders (vendor, .git, ...) are always excluded,
        // even if they're not in config
        $this->assertNotEquals(2, count($config['exclude_folders']));
    }
}
