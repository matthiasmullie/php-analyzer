<?php

namespace Cauditor\Analyzers;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class Converter
{
    /**
     * Project-wide totals.
     *
     * @var array
     */
    protected $totals = array();

    /**
     * Processes XML node by node, extracts the relevant metrics & writes them
     * piecemeal to a JSON file.
     *
     * @param XMLReader $reader XML document to read from.
     *
     * @return string
     */
    public function convert(XMLReader $reader)
    {
        $reader->readNext('metrics');

        $this->totals = array();
        $packages = $this->convertPackages($reader);

        $data = array(
                'loc' => $reader->getAttribute('eloc'),
                'noc' => $reader->getAttribute('noc'),
                'nom' => $reader->getAttribute('nom'),
            ) + $this->totals;

        $json = json_encode($data);
        // data will be stored as json, but we need to inject children, so
        // don't write the closing `]` & `}` yet
        return substr($json, 0, -1).',"children":['.$packages.']}';
    }

    protected function convertPackages(XMLReader $reader)
    {
        $i = 0;
        $content = '';
        while ($reader->readNext('package', 'metrics')) {
            $data = array('name' => $reader->getAttribute('name'));

            $this->addToTotals($data);

            $json = json_encode($data);
            // add `,` between multiple nodes
            $json = $i++ === 0 ? $json : ','.$json;
            // data will be stored as json, but we need to fetch children, so
            // don't write the closing `]` & `}` yet
            $content .= substr($json, 0, -1).',"children":['.$this->convertClasses($reader).']}';
        }

        return $content;
    }

    protected function convertClasses(XMLReader $reader)
    {
        $i = 0;
        $content = '';
        while ($reader->readNext('class', 'package')) {
            $data = array(
                'name' => $reader->getAttribute('name'),
                'loc' => (int) $reader->getAttribute('eloc'),
                'ca' => (int) $reader->getAttribute('ca'),
                'ce' => (int) $reader->getAttribute('ce'),
                'i' => (float) number_format((int) $reader->getAttribute('ce') / (((int) $reader->getAttribute('ce') + (int) $reader->getAttribute('ca')) ?: 1), 2),
                'dit' => (int) $reader->getAttribute('dit'),
            );

            $this->addToTotals($data);

            $json = json_encode($data);
            // add `,` between multiple nodes
            $json = $i++ === 0 ? $json : ','.$json;
            // data will be stored as json, but we need to fetch children, so
            // don't write the closing `]` & `}` yet
            $content .= substr($json, 0, -1).',"children":['.$this->convertMethods($reader).']}';
        }

        return $content;
    }

    protected function convertMethods(XMLReader $reader)
    {
        $i = 0;
        $content = '';
        while ($reader->readNext('method', 'class')) {
            $data = array(
                'name' => $reader->getAttribute('name'),
                'loc' => (int) $reader->getAttribute('eloc'),
                'ccn' => (int) $reader->getAttribute('ccn2'),
                'npath' => (int) $reader->getAttribute('npath'),
                'he' => (float) number_format($reader->getAttribute('he'), 2),
                'hi' => (float) number_format($reader->getAttribute('hi'), 2),
                'mi' => (float) number_format($reader->getAttribute('mi'), 2),
            );

            $this->addToTotals($data);

            $json = json_encode($data);
            // add `,` between multiple nodes
            $json = $i++ === 0 ? $json : ','.$json;

            $content .= $json;
        }

        return $content;
    }

    protected function addToTotals(array $data)
    {
        // don't need these, obviously...
        unset($data['name'], $data['loc']);

        foreach ($data as $metric => $value) {
            if (!isset($this->totals[$metric])) {
                $this->totals[$metric] = 0;
            }

            $this->totals[$metric] += $value;
        }
    }
}
