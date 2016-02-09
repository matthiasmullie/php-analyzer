<?php

namespace Cauditor;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class Converter
{
    /**
     * @var XMLReader
     */
    protected $reader;

    /**
     * @var resource
     */
    protected $handle;

    /**
     * @var bool
     */
    protected $once = false;

    /**
     * @param XMLReader $reader XML document to read from.
     * @param resource  $handle File to write converted JSON to.
     */
    public function __construct(XMLReader $reader, $handle)
    {
        $this->reader = $reader;
        $this->handle = $handle;
    }

    /**
     * Processes XML node by node, extracts the relevant metrics & writes them
     * piecemeal to a JSON file.
     *
     * @throws Exception
     */
    public function convert()
    {
        // converting more than once is not allowed:
        // * XMLReader can't rewind
        // * JSON would be malformed if it got this output more than once
        if ($this->once !== false) {
            throw new Exception('Unable to convert more than once, please start over.');
        }
        $this->once = true;

        $this->convertProject();
    }

    protected function convertProject()
    {
        $this->reader->readNext('metrics');

        $data = array(
            'loc' => $this->reader->getAttribute('eloc'),
            'noc' => $this->reader->getAttribute('noc'),
            'nom' => $this->reader->getAttribute('nom'),
        );

        $json = json_encode($data);
        // data will be stored as json, but we need to fetch children, so
        // don't write the closing `]` & `}` yet
        $json = substr($json, 0, -1).',"children":[';

        fwrite($this->handle, $json);

        $this->convertPackages();

        fwrite($this->handle, ']}');
    }

    protected function convertPackages()
    {
        $i = 0;
        while ($this->reader->readNext('package', 'metrics')) {
            $data = array('name' => $this->reader->getAttribute('name'));

            $json = json_encode($data);
            // data will be stored as json, but we need to fetch children, so
            // don't write the closing `]` & `}` yet
            $json = substr($json, 0, -1).',"children":[';
            // add `,` between multiple nodes
            $json = $i++ === 0 ? $json : ','.$json;

            fwrite($this->handle, $json);

            $this->convertClasses();

            fwrite($this->handle, ']}');
        }
    }

    protected function convertClasses()
    {
        $i = 0;
        while ($this->reader->readNext('class', 'package')) {
            $data = array(
                'name' => $this->reader->getAttribute('name'),
                'loc' => (int) $this->reader->getAttribute('eloc'),
                'ca' => (int) $this->reader->getAttribute('ca'),
                'ce' => (int) $this->reader->getAttribute('ce'),
                'i' => (float) number_format((int) $this->reader->getAttribute('ce') / (((int) $this->reader->getAttribute('ce') + (int) $this->reader->getAttribute('ca')) ?: 1), 2),
                'dit' => (int) $this->reader->getAttribute('dit'),
            );

            $json = json_encode($data);
            // data will be stored as json, but we need to fetch children, so
            // don't write the closing `]` & `}` yet
            $json = substr($json, 0, -1).',"children":[';
            // add `,` between multiple nodes
            $json = $i++ === 0 ? $json : ','.$json;

            fwrite($this->handle, $json);

            $this->convertMethods();

            fwrite($this->handle, ']}');
        }
    }

    protected function convertMethods()
    {
        $i = 0;
        while ($this->reader->readNext('method', 'class')) {
            $data = array(
                'name' => $this->reader->getAttribute('name'),
                'loc' => (int) $this->reader->getAttribute('eloc'),
                'ccn' => (int) $this->reader->getAttribute('ccn2'),
                'npath' => (int) $this->reader->getAttribute('npath'),
                'he' => (float) number_format($this->reader->getAttribute('he'), 2),
                'hi' => (float) number_format($this->reader->getAttribute('hi'), 2),
                'mi' => (float) number_format($this->reader->getAttribute('mi'), 2),
            );
            $json = json_encode($data);
            // add `,` between multiple nodes
            $json = $i++ === 0 ? $json : ','.$json;

            fwrite($this->handle, $json);
        }
    }
}
