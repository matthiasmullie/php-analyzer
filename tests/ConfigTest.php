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

        $this->path = realpath(__DIR__.'/config');
    }

    public function testNoFile()
    {
        $config = new Config($this->path);

        // confirm that default value is used when no config is specified
        $this->assertEquals('build/cauditor', $config['build_path']);
    }

    public function testInvalidFile()
    {
        $config = new Config($this->path, $this->path.'/non-existing.yml');

        // confirm that default value is used when invalid config is specified
        $this->assertEquals('build/cauditor', $config['build_path']);
    }

    public function testOverrideFile()
    {
        $config = new Config($this->path, $this->path.'/override.yml');

        // confirm that config-specified value is used
        $this->assertEquals('custom/build/path', $config['build_path']);
    }

    public function testEmptyFile()
    {
        $config = new Config($this->path, $this->path.'/empty.yml');

        // confirm that default value is used for what's not in config
        $this->assertEquals('build/cauditor', $config['build_path']);
    }

    public function testEnforceExcludeFolders()
    {
        $config = new Config($this->path, $this->path.'/override.yml');

        // confirm that some folders (vendor, .git, ...) are always excluded,
        // even if they're not in config
        $this->assertNotEquals(2, count($config['exclude_folders']));
    }

    public function testAdaptToChangingConfig()
    {
        $config = new Config($this->path, $this->path.'/change.yml');

        // change.yml doesn't yet exist, so defaults should be used
        $this->assertEquals('build/cauditor', $config['build_path']);

        // change.yml now exists & overrides some config
        $content = file_get_contents($this->path.'/override.yml');
        file_put_contents($this->path.'/change.yml', $content);
        $this->assertEquals('custom/build/path', $config['build_path']);

        // change.yml changes again & has no overrides, so should use defaults
        $content = file_get_contents($this->path.'/empty.yml');
        file_put_contents($this->path.'/change.yml', $content);
        $this->assertEquals('build/cauditor', $config['build_path']);
    }
}
