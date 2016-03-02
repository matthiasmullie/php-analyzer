<?php

namespace Cauditor\Runners;

use Cauditor\Analyzers\AnalyzerInterface;
use Cauditor\Config;
use Cauditor\Api;
use Cauditor\Exception;
use MatthiasMullie\CI\Factory as CIFactory;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class Current implements RunnerInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Api
     */
    protected $api;

    /**
     * @var AnalyzerInterface
     */
    protected $analyzer;

    /**
     * @param Config            $config
     * @param Api               $api
     * @param AnalyzerInterface $analyzer
     */
    public function __construct(Config $config, Api $api, AnalyzerInterface $analyzer)
    {
        $this->config = $config;
        $this->api = $api;
        $this->analyzer = $analyzer;
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $output = exec('git stash', $o, $e);
        var_dump($output);
        var_dump($e);
        exit();

        $metrics = $this->analyzer->execute();

        $data = array('metrics' => $metrics) + $this->getEnvironment();

        // submit to cauditor (note that branch can be empty for PRs)
        $uri = "/api/v1/{$data['slug']}/{$data['branch']}/{$data['commit']}";
        $uri = preg_replace('/(?<!:)\/+/', '/', $uri);

        $result = $this->api->put($uri, $data);

        if ($result !== false) {
            echo "Submitted metrics for {$data['commit']} @ {$data['slug']} {$data['branch']}\n";
        } else {
            echo "Failed to submit metrics for {$data['commit']} @ {$data['slug']} {$data['branch']}\n";
        }
    }

    /**
     * @return array
     */
    protected function getEnvironment()
    {
        // get build data from CI
        $factory = new CIFactory();
        $environment = $factory->getCurrent();

        return array(
            'repo' => $environment->getRepo(),
            'slug' => $environment->getSlug(),
            'branch' => $environment->getBranch(),
            'pull-request' => $environment->getPullRequest(),
            'commit' => $environment->getCommit(),
            'previous-commit' => $environment->getPreviousCommit(),
            'author-email' => $environment->getAuthorEmail(),
            'timestamp' => $environment->getTimestamp(),
        );
    }
}
