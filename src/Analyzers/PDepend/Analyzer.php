<?php

namespace Cauditor\Analyzers\PDepend;

use Cauditor\Analyzers\AnalyzerInterface;
use Cauditor\Config;
use Cauditor\Exception;
use MatthiasMullie\PathConverter\Converter as PathConverter;
use PDepend\Application;
use PDepend\Input\ExcludePathFilter;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class Analyzer implements AnalyzerInterface
{
    /**
     * cauditor JSON filename.
     *
     * @var string
     */
    protected $json = 'cauditor.json';

    /**
     * @var string
     */
    protected $buildPath;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->setConfig($config);
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        // all paths in build_path are relative to project root, which may not
        // be where this code is run from, so prepend the project root!
        $this->buildPath = $this->config['path'].DIRECTORY_SEPARATOR.$this->config['build_path'];
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    public function execute()
    {
        exec('mkdir -p '.$this->buildPath, $output, $result);
        if ($result !== 0) {
            throw new Exception('Unable to create build directory.');
        }

        // let pdepend generate all metrics we'll need
        $this->pdepend();

        // if we expect these json files to be loaded client-side to render
        // the charts, might as well assume it'll fit in this machine's
        // memory to submit it to our API ;)
        $json = file_get_contents($this->buildPath.DIRECTORY_SEPARATOR.$this->json);

        return json_decode($json);
    }

    /**
     * Runs pdepend to generate the metrics.
     *
     * @throws Exception
     */
    protected function pdepend()
    {
        $jsonGenerator = new JsonGenerator();
        $jsonGenerator->setLogFile($this->buildPath.DIRECTORY_SEPARATOR.$this->json);

        $application = new Application();
        $engine = $application->getEngine();
        $engine->addReportGenerator($jsonGenerator);

        $engine->addDirectory($this->config['path']);

        // exclude directories are evaluated relative to where pdepend is being
        // run from, not what it is running on
        $converter = new PathConverter($this->config['path'], getcwd());
        $exclude = array_map(array($converter, 'convert'), $this->config['exclude_folders']);
        $filter = new ExcludePathFilter($exclude);
        $engine->addFileFilter($filter);

        try {
            $engine->analyze();
        } catch (\Exception $e) {
            throw new Exception('Unable to generate pdepend metrics.');
        }
    }
}
