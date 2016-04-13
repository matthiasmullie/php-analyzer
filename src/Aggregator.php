<?php

namespace Cauditor;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class Aggregator
{
    /**
     * @var array
     */
    protected $metrics;

    /**
     * @var float[][]
     */
    protected $flat;

    /**
     * @param array $metrics An AnalyzerInterface::execute result
     */
    public function __construct($metrics)
    {
        $this->metrics = $metrics;
    }

    /**
     * Get metric averages.
     *
     * @return float[]
     */
    public function average()
    {
        $flat = $this->flatten();

        $avg = array();
        foreach ($flat as $metric => $values) {
            $avg[$metric] = (float) number_format(array_sum($values) / count($values), 2, '.', '');
        }

        return $avg;
    }

    /**
     * Get metric minima.
     *
     * @return float[]
     */
    public function min()
    {
        $flat = $this->flatten();

        return array_map('min', $flat);
    }

    /**
     * Get metric maxima.
     *
     * @return float[]
     */
    public function max()
    {
        $flat = $this->flatten();

        return array_map('max', $flat);
    }

    /**
     * Get weighed metric averages, where the bigger a method/class is, the more
     * it's metric value will count towards the result.
     *
     * @return float[]
     */
    public function weigh()
    {
        $flat = $this->flatten();

        $weighed = array();
        foreach ($flat as $metric => $data) {
            $weighed[$metric] = 0;
            $relevant = 0;
            foreach ($data as $name => $value) {
                $loc = isset($flat['loc'][$name]) ? $flat['loc'][$name] : 0;
                $weighed[$metric] += $value * $loc;
                $relevant += $loc;
            }

            $weighed[$metric] = $weighed[$metric] / ($relevant ?: 1);
        }

        return $weighed;
    }

    /**
     * Flatten metrics into [metric => [value1, value1, value3]] array.
     *
     * @return float[][]
     */
    protected function flatten()
    {
        if (!$this->flat) {
            $this->flat = $this->recurse($this->metrics);
        }

        return $this->flat;
    }

    /**
     * @param array $metrics
     *
     * @return float[][]
     */
    protected function recurse(array $metrics)
    {
        $flat = array();
        $metrics = (array) $metrics;

        foreach ($metrics as $metric => $value) {
            // name & children are meta data, not metrics
            if (!in_array($metric, array('name', 'children'))) {
                $name = isset($metrics['name']) ? $metrics['name'] : '';
                $flat[$metric][$name] = $value;
            }
        }

        if (isset($metrics['children'])) {
            foreach ($metrics['children'] as $child) {
                $childTotals = $this->recurse($child);
                $flat = array_merge_recursive($flat, $childTotals);
            }
        }

        foreach ($flat as $metric => $data) {
            // array_merge_recursive will merge into an array of values if there
            // are multiple things with the same name, in which case we'll just
            // go with the last occurrence
            $flat[$metric] = array_map(function ($value) {
                return is_array($value) ? array_pop($value) : $value;
            }, $flat[$metric]);
        }

        return $flat;
    }
}
