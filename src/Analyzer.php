<?php

namespace Cauditor;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class Analyzer
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
     *
     * @throws Exception
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        // all paths in build_path are relative to project root, which may not
        // be where this code is run from, so prepend the project root!
        $this->buildPath = $this->config['path'].DIRECTORY_SEPARATOR.$this->config['build_path'];
    }

    /**
     * @param string $api
     *
     * @return string|bool API response (on success) or false (on failure)
     *
     * @throws Exception
     */
    public function run($api)
    {
        exec('mkdir -p '.$this->buildPath, $output, $result);
        if ($result !== 0) {
            throw new Exception('Unable to create build directory.');
        }

        $this->pdepend();
        $this->convert();

        return $this->transmit($api);
    }

    /**
     * Runs pdepend to generate the metrics.
     *
     * @throws Exception
     */
    protected function pdepend()
    {
        $path = $this->config['path'];
        $xml = $this->buildPath.DIRECTORY_SEPARATOR.$this->xml;
        $exclude = implode(',', $this->config['exclude_folders']);

        $command = "vendor/bin/pdepend --summary-xml=$xml --ignore=$exclude $path";
        exec($command, $output, $result);
        if ($result !== 0) {
            throw new Exception('Unable to generate pdepend metrics.');
        }
    }

    /**
     * Transform pdepend output into the (more succinct) format cauditor
     * understands.
     *
     * @throws Exception
     */
    protected function convert()
    {
        $xml = $this->buildPath.DIRECTORY_SEPARATOR.$this->xml;
        $json = $this->buildPath.DIRECTORY_SEPARATOR.$this->json;

        $reader = new XMLReader();
        $reader->open($xml);

        $handle = fopen($json, 'w');

        $converter = new Converter($reader, $handle);
        $converter->convert();

        fclose($handle);
        $reader->close();
    }

    /**
     * Submit the file to cauditor API.
     *
     * @param string $api
     *
     * @return string|bool API response (on success) or false (on failure)
     */
    protected function transmit($api)
    {
        $handle = fopen($this->buildPath.DIRECTORY_SEPARATOR.$this->json, 'r');
        $options = array(
            CURLOPT_URL => $api,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_PUT => 1,
            CURLOPT_INFILE => $handle,
            CURLOPT_RETURNTRANSFER => 1,
        );

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        curl_close($curl);
        fclose($handle);

        return $result;
    }
}
