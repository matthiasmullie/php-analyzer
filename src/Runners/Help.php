<?php

namespace Cauditor\Runners;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class Help implements RunnerInterface
{
    public function execute()
    {
        echo "Usage: cauditor [-ah] [path]\n\n",
            "  --all           Analyzes all missing commits (instead of only the current).\n",
            "  --path=<dir>    Analyze a specific directory (instead of pwd).\n",
            "  --repo=<uri>    Analyze a specific repo (instead of the one in pwd).\n",
            "  --help          Prints this help message.\n";
    }
}
