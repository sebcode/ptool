<?php

class App
{
    private $ptool;
    private $args;

    public function __construct()
    {
        $this->ptool = new PTool\PTool();
    }

    public function commandHelp()
    {
        echo "syntax: pt [project]\n";
    }

    public function commandShow()
    {
        foreach ($this->ptool->getProjects() as $project) {
            if ($this->ptool->isCurrent($project)) {
                echo '['.$project->handle.'] ';
            } else {
                echo $project->handle.' ';
            }
        }
        echo "\n";
    }

    public function commandSelect($handle)
    {
        if (!$project = $this->ptool->getProject($handle)) {
            throw new Exception('no such project');
        }

        $this->ptool->setCurrentProject($project);
    }

    public function process($args)
    {
        $this->args = $args;

        if (count($args) <= 1) {
            return $this->commandShow();
        }

        $this->requireArgs(1);

        $cmd = $args[1];

        switch ($cmd) {
            case '-h':
            case '-help':
            case '--help':
                return $this->commandHelp();
        }

        $this->commandSelect($args[1]);
        $this->commandShow();
    }

    private function requireArgs($num)
    {
        if (count($this->args) < $num + 1) {
            throw new Exception($num.' arguments required.');
        }
    }
}
