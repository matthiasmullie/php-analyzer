<?php

namespace Cauditor;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class Utils
{
    /**
     * `getopts` is very convenient, but annoying how one has to check for both
     * the long & short versions for values; this will always return an array
     * with the long opt as key, even if the user passed it as short.
     *
     * @param array $options [short => long]
     *
     * @return array
     */
    public static function getopts($options)
    {
        $opts = getopt(implode('', array_keys($options)), $options);

        $result = array();
        foreach ($options as $short => $long) {
            $short = trim($short, ':');
            $long = trim($long, ':');

            if (isset($opts[$short])) {
                $input[$long] = $opts[$short];
            } elseif (isset($opts[$long])) {
                $input[$long] = $opts[$long];
            }
        }

        return $result;
    }
}
