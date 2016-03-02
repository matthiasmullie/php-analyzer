<?php

namespace Cauditor\Runners;

use Cauditor\Analyzers\AnalyzerInterface;
use Cauditor\Api;
use Cauditor\Config;
use Cauditor\Exception;
use MatthiasMullie\CI\Factory as CIFactory;

/**
 * @author Matthias Mullie <cauditor@mullie.eu>
 * @copyright Copyright (c) 2016, Matthias Mullie. All rights reserved.
 * @license LICENSE MIT
 */
class All implements RunnerInterface
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
     * @var bool
     */
    protected $stashed;

    /**
     * @var string
     */
    protected $branch;

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

        // lets make sure the code we're testing is this specific commit,
        // without lingering uncommitted bits
        $output = exec('git stash');
        $this->stashed = $output === 'No local changes to save';

        // store current branch
        $environment = $this->getEnvironment();
        $this->branch = $environment['branch'];

        // move to default branch, which is likely the one we want to analyze
        $this->setBranch($this->getDefaultBranch());
    }

    /**
     * Restores the original environment after analyzing.
     */
    public function __destruct()
    {
        exec("git checkout $this->branch");
        if ($this->stashed) {
            exec('git stash pop');
        }
    }

    /**
     * @param string $branch
     */
    public function setBranch($branch)
    {
        exec("git checkout $branch && git reset --hard && git pull");
    }

    /**
     * @return string[]
     */
    protected function getCommits()
    {
        exec("git log --pretty=format:'%H'", $commits);

        return $commits;
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        // exclude commits that have already been imported
        $commits = $this->getCommits();
        $imported = $this->getImportedCommits();
        $missing = array_diff($commits, $imported);
        foreach ($missing as $commit) {
            exec("git reset $commit --hard");

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

        echo "Done!\n";
    }

    /**
     * Returns array of commit hashes that have already been imported.
     *
     * @return string[]
     *
     * @throws Exception
     */
    protected function getImportedCommits()
    {
        $environment = $this->getEnvironment();
        $slug = $environment['slug'];
        $branch = $environment['branch'];

        $imported = $this->api->get("/api/v1/$slug/$branch");
        if ($imported === false) {
            throw new Exception('Failed to reach API.');
        }

        return json_decode($imported);
    }

    /**
     * @return string
     */
    protected function getDefaultBranch()
    {
        $config = shell_exec('cat .git/config');
        preg_match('/\[branch "(.+)"\]/', $config, $match);

        return isset($match[1]) ? $match[1] : 'master';
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
