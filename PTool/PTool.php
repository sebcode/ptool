<?php

namespace PTool;

class PTool
{
    protected $basePath;
    protected $projects;

    public function __construct($basePath = false)
    {
        if (!$basePath) {
            $this->basePath = $_SERVER['HOME'] . '/dev/';
        } else {
            $this->basePath = $basePath;
        }
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getProjects()
    {
        if (is_array($this->projects)) {
            return $this->projects;
        }

        $dirs = [ $this->basePath ];

        if (file_exists($f = $this->basePath . '/.ptooldirs')) {
            foreach (file($f) as $subdir) {
                $subdir = trim($subdir);
                if (empty($subdir)) {
                    continue;
                }
                $dirs[] = $this->basePath . '/' . $subdir;
            }
        }

        $projects = [];

        foreach ($dirs as $dir) {
            foreach (glob($dir . '/*', GLOB_ONLYDIR) as $path) {
                if (!file_exists($path . '/.alias')) {
                    continue;
                }

                if ($project = Project::createFromPath($path . '/')) {
                    $projects[$project->handle] = $project;
                }
            }
        }

        $this->projects = $projects;
        return $projects;
    }

    public function getProject($handle)
    {
        $this->getProjects();
        if (isset($this->projects[$handle])) {
            return $this->projects[$handle];
        }

        throw new \Exception('no such project');
    }

    public function getCurrentProject($silent = false)
    {
        if (!file_exists($this->basePath . '.current')) {
            if ($silent) {
                return false;
            } else {
                throw new \Exception('no project selected');
            }
        }

        $currentHandle = trim(file_get_contents($this->basePath . '.current'));
        return $this->getProject($currentHandle);
    }

    public function setCurrentProject(Project $project)
    {
        file_put_contents($this->basePath . '.current', $project->handle);
    }

    public function isCurrent(Project $project)
    {
        return $this->getCurrentProject() === $project;
    }

}
