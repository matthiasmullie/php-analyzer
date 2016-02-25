<?php

namespace Cauditor\Analyzers;

use Cauditor\Config;
use Cauditor\Exception;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
interface AnalyzerInterface
{
    /**
     * @param Config $config
     */
    public function setConfig(Config $config);

    /**
     * @return array
     *
     * @throws Exception
     */
    public function execute();
}
