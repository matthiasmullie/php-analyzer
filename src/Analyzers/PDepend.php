<?php

namespace Cauditor\Analyzers;

use Cauditor\Config;
use Cauditor\Exception;
use MatthiasMullie\PathConverter\Converter as PathConverter;
use PDepend\Application;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class PDepend implements AnalyzerInterface
{
    /**
     * pdepend XML filename.
     *
     * @var string
     */
    protected $xml = 'pdepend.xml';

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

        // convert pdepend xml into cauditor json & store to file
        $json = $this->convert($this->buildPath.DIRECTORY_SEPARATOR.$this->xml);
        file_put_contents($this->buildPath.DIRECTORY_SEPARATOR.$this->json, $json);

        // if we expect these json files to be loaded client-side to render
        // the charts, might as well assume it'll fit in this machine's
        // memory to submit it to our API ;)
        return json_decode($json);
    }

    /**
     * Runs pdepend to generate the metrics.
     *
     * @throws Exception
     */
    protected function pdepend()
    {
        $application = new Application();
        $runner = $application->getRunner();

        $runner->setSourceArguments(array($this->config['path']));
        $runner->addReportGenerator('summary-xml', $this->buildPath.DIRECTORY_SEPARATOR.$this->xml);

        // exclude directories are evaluated relative to where pdepend is being
        // run from, not what it is running on
        $converter = new PathConverter($this->config['path'], getcwd());
        $exclude = array_map(array($converter, 'convert'), $this->config['exclude_folders']);
        $runner->setExcludeDirectories($exclude);

        $status = $runner->run();
        if ($status !== 0) {
            throw new Exception('Unable to generate pdepend metrics.');
        }
    }

    /**
     * Transform pdepend output into the (more succinct) format cauditor
     * understands.
     *
     * @param string $xml
     *
     * @return string
     */
    protected function convert($xml)
    {
        $reader = new XMLReader();
        $reader->open($xml);

        $converter = new Converter();
        $json = $converter->convert($reader);

        $reader->close();

        return $json;
    }
}
