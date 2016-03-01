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
class All extends Current implements RunnerInterface
{
    /**
     * @var string
     */
    protected $slug;

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
        parent::__construct($config, $api, $analyzer);

        // figure out current repo & branch
        $this->slug = $this->getRepo();
        $this->branch = $this->getDefaultBranch();
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        // find build folder
        $path = $this->config['path'];
        $build = $path.DIRECTORY_SEPARATOR.$this->config['build_path'];

        // don't want to mess with current repo; copy it to build folder instead
        exec("rm -rf $build/repo && mkdir -p $build/repo && cp -r $path/.git $build/repo/.git");
        chdir("$build/repo");

        // checkout default branch & get list of all commits
        exec("git checkout {$this->branch} && git reset --hard && git pull");
        exec("git log --pretty=format:'%H'", $commits);

        // compare with already imported commits & figure out which are missing
        $imported = $this->getImportedCommits($this->slug, $this->branch);
        $missing = array_diff($commits, $imported);

        // now analyze all missing commits
        foreach ($missing as $commit) {
            exec("git reset $commit --hard");

            $config = new Config(getcwd(), getcwd().DIRECTORY_SEPARATOR.'.cauditor.yml');
            $this->analyzer->setConfig($config);

            parent::execute();
        }

        // cleanup
        chdir('../..');
        exec("rm -rf $build/repo");
    }

    /**
     * @return string
     */
    protected function getRepo()
    {
        $factory = new CIFactory();
        $ci = $factory->getCurrent();

        return $ci->getSlug();
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
     * @param string $slug
     * @param string $branch
     *
     * @return string[]
     *
     * @throws Exception
     */
    protected function getImportedCommits($slug, $branch)
    {
        $imported = $this->api->get("/api/v1/$slug/$branch");
        if ($imported === false) {
            throw new Exception('Failed to reach API.');
        }

        return json_decode($imported);
    }
}
