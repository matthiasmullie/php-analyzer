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
     * Processes XML node by node, extracts the relevant metrics & writes them
     * piecemeal to a JSON file.
     *
     * @param XMLReader $reader XML document to read from.
     *
     * @return string
     *
     * @throws Exception
     */
    public function convert(XMLReader $reader)
    {
        $reader->readNext('metrics');

        $data = array(
            'loc' => $reader->getAttribute('eloc'),
            'noc' => $reader->getAttribute('noc'),
            'nom' => $reader->getAttribute('nom'),
        );

        $json = json_encode($data);
        // data will be stored as json, but we need to fetch children, so
        // don't write the closing `]` & `}` yet
        return substr($json, 0, -1).',"children":['.$this->convertPackages($reader).']}';
    }

    protected function convertPackages(XMLReader $reader)
    {
        $i = 0;
        $content = '';
        while ($reader->readNext('package', 'metrics')) {
            $data = array('name' => $reader->getAttribute('name'));

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
            $json = json_encode($data);
            // add `,` between multiple nodes
            $json = $i++ === 0 ? $json : ','.$json;

            $content .= $json;
        }

        return $content;
    }
}
