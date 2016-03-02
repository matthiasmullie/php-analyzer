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
        echo "Usage: cauditor [options]\n\n",
            "Commit Options: (default = analyze latest commit)\n\n",
            "  -a|--all              Analyzes all missing commits.\n",
            "  -c|--commits=<hash>   Analyze specific (comma-separated list of) commits.\n\n",
            "Repo Options: (default = analyze default branch of repo in `pwd`)\n\n",
            "  -r|--repo=<uri>       Analyze a specific repo.\n",
            "  -b|--branch=<branch>  Analyze a specific branch.\n",
            "  -p|--path=<dir>       Analyze a specific directory.\n\n",
            "Miscellaneous:\n\n",
            "  -h|--help             Prints this help message.\n";
    }
}
