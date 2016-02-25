<?php

namespace Cauditor\Runners;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
interface RunnerInterface
{
    /**
     * @throws \Cauditor\Exception
     */
    public function execute();
}
