<?php

namespace Cauditor;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class Api
{
    /**
     * @var string
     */
    protected $api;

    /**
     * @param string $api
     */
    public function __construct($api)
    {
        $this->api = $api;
    }

    /**
     * Read data from cauditor API.
     *
     * @param string $uri
     *
     * @return string|bool API response (on success) or false (on failure)
     */
    public function get($uri)
    {
        $options = array(
            CURLOPT_URL => $this->api . $uri,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
        );

        return $this->exec($options);
    }

    /**
     * Submit the data to cauditor API.
     *
     * @param string $uri
     * @param array  $data
     *
     * @return string|bool API response (on success) or false (on failure)
     */
    public function put($uri, array $data)
    {
        // PUT requests need an fopen wrapper, so we'll create a temporary one
        // for the data to submit...
        $json = json_encode($data);
        $file = fopen('php://temp', 'w+');
        fwrite($file, $json, strlen($json));
        fseek($file, 0);

        $options = array(
            CURLOPT_URL => $this->api . $uri,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_PUT => 1,
            CURLOPT_INFILE => $file,
            CURLOPT_INFILESIZE => strlen($json),
        );

        $result = $this->exec($options);

        fclose($file);

        return $result;
    }

    /**
     * @param array $options array of CURLOPT_* options
     *
     * @return string|bool API response (on success) or false (on failure)
     */
    protected function exec($options)
    {
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
}
