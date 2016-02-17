<?php

namespace Cauditor;

use MatthiasMullie\PathConverter\Converter as PathConverter;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class Config implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var array
     */
    protected $defaults = array(
        'build_path' => 'build/cauditor',
        'exclude_folders' => array('tests', 'vendor'),
    );

    /**
     * List of config keys that denote paths, so they can be normalized
     * (= turned into "relative to project root" instead of "relative to config
     * file").
     *
     * @var string[]
     */
    protected $paths = array('build_path', 'exclude_folders');

    /**
     * @param string $project Project root path.
     * @param string $config  Config YML file path.
     */
    public function __construct($project, $config = null)
    {
        $this->config['path'] = rtrim($project, DIRECTORY_SEPARATOR);
        $this->config['config_path'] = null;

        if ($config !== null) {
            $this->config['config_path'] = rtrim($config, DIRECTORY_SEPARATOR);
            $this->config += $this->readConfig($config);
        }

        $this->config += $this->defaults;

        // *always* exclude some folders - they're not project-specific code and
        // could easily be overlooked when overriding excludes
        $this->config['exclude_folders'][] = 'vendor';
        $this->config['exclude_folders'][] = '.git';
        $this->config['exclude_folders'][] = '.svn';
        $this->config['exclude_folders'] = array_unique($this->config['exclude_folders']);
    }

    /**
     * Normalize all relative paths by prefixing them with the project path.
     *
     * @param string|string[] $value
     *
     * @return string|string[]
     */
    protected function normalizePath($value)
    {
        // array of paths = recursive
        if (is_array($value)) {
            foreach ($value as $i => $val) {
                $value[$i] = $this->normalizePath($val);
            }

            return $value;
        }

        $converter = new PathConverter(dirname($this->config['config_path']), $this->config['path']);

        return $converter->convert($value);
    }

    /**
     * @param string $path Path to config file.
     *
     * @return array
     */
    protected function readConfig($path)
    {
        if (!file_exists($path) || !is_file($path) || !is_readable($path)) {
            return array();
        }

        $yaml = new Parser();

        try {
            $config = (array) $yaml->parse(file_get_contents($path));
        } catch (ParseException $e) {
            return array();
        }

        // adjust relative paths
        foreach ($this->paths as $key) {
            if (isset($config[$key])) {
                $config[$key] = $this->normalizePath($config[$key]);
            }
        }

        return $config;
    }

    /**
     * {@inheritdoc.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->config);
    }

    /**
     * {@inheritdoc.
     */
    public function offsetGet($offset)
    {
        return $this->config[$offset];
    }

    /**
     * {@inheritdoc.
     */
    public function offsetSet($offset, $value)
    {
        $this->config[$offset] = $value;
    }

    /**
     * {@inheritdoc.
     */
    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }
}
