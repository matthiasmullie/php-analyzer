<?php

namespace Cauditor\Runners;

use Cauditor\Analyzers\AnalyzerInterface;
use Cauditor\Api;
use Cauditor\Config;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class Commits extends All implements RunnerInterface
{
    /**
     * @var array
     */
    protected $commits = array();

    /**
     * {@inheritdoc}
     *
     * @param array $commits
     */
    public function __construct(Api $api, AnalyzerInterface $analyzer, array $commits)
    {
        parent::__construct($api, $analyzer);

        $this->commits = $commits;
    }

    /**
     * @return string[]
     */
    protected function getCommits()
    {
        return $this->commits;
    }
}
