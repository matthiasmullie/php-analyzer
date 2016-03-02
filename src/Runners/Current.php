<?php

namespace Cauditor\Runners;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class Current extends All implements RunnerInterface
{
    /**
     * @return string[]
     */
    protected function getCommits()
    {
        exec("git log --pretty=format:'%H' -1", $commits);

        return $commits;
    }
}
