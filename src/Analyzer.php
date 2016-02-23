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

        // let pdepend generate all metrics we'll need
        $this->pdepend();

        // convert pdepend xml into cauditor json & store to file
        $json = $this->convert($this->buildPath.DIRECTORY_SEPARATOR.$this->xml);
        file_put_contents($this->buildPath.DIRECTORY_SEPARATOR.$this->json, $json);

        $data = $this->sniff();
        // if we expect these json files to be loaded client-side to render
        // the charts, might as well assume it'll fit in this machine's
        // memory to submit it to our API ;)
        $data['json'] = json_decode($json);

        return $this->transmit($api, $data);
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
     * Fetch build data from CI.
     *
     * @return string[]
     *
     * @throws Exception
     */
    protected function sniff()
    {
        $build = exec('vendor/bin/ci-sniffer', $output, $result);
        if ($result !== 0) {
            throw new Exception('Unable to get build details.');
        }

        return (array) json_decode($build);
    }

    /**
     * Transform pdepend output into the (more succinct) format cauditor
     * understands.
     *
     * @param string $xml
     *
     * @return string
     *
     * @throws Exception
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

    /**
     * Submit the data to cauditor API.
     *
     * @param string $api
     * @param array  $data
     *
     * @return string|bool API response (on success) or false (on failure)
     */
    protected function transmit($api, array $data)
    {
        // PUT requests need a fopen wrapper, so we'll create a temporary one
        // for the data to submit...
        $json = json_encode($data);
        $file = fopen('php://temp', 'w+');
        fwrite($file, $json, strlen($json));
        fseek($file, 0);

        // url needs some variable data, so we'll parse it in
        $api = preg_replace_callback('/\{([a-z0-9]+)\}/i', function ($match) use ($data) {
            return isset($data[$match[1]]) ? $data[$match[1]] : $match[0];
        }, $api);
        // if parts of url are now empty, just omit them (e.g. branch could be)
        $api = preg_replace('/(?<!:)\/+/', '/', $api);

        $options = array(
            CURLOPT_URL => $api,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_PUT => 1,
            CURLOPT_INFILE => $file,
            CURLOPT_INFILESIZE => strlen($json),
        );

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        curl_close($curl);

        fclose($file);

        return $result;
    }
}
