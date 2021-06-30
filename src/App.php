<?php

namespace PTool;

class App
{
    private $ptool;
    private $args;

    public function __construct()
    {
        $this->ptool = new PTool();
    }

    public function commandHelp()
    {
        echo "syntax: pt [project]\n";
    }

    public function commandShow()
    {
        foreach ($this->ptool->getProjects() as $project) {
            if ($this->ptool->isCurrent($project)) {
                echo '['.$project->handle.']' . "\t";
            } else {
                echo $project->handle."\t";
            }
            if ($project->desc) {
                echo " - ".$project->desc."\n";
            } else {
                echo "\n";
            }
        }
        echo "\n";
    }

    public function commandSelect($handle)
    {
        if (!$project = $this->ptool->getProject($handle)) {
            throw new \Exception('no such project');
        }

        $this->ptool->setCurrentProject($project);
        echo "Selected: {$project->handle}\n";
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
    }

    private function requireArgs($num)
    {
        if (count($this->args) < $num + 1) {
            throw new \Exception($num.' arguments required.');
        }
    }
}
