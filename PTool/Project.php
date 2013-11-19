<?php

namespace PTool;

class Project
{

    public $handle;
    public $path;

    public static function createFromPath($path)
    {
        $project = new Project;
        $project->handle = basename($path);
        $project->path = $path;

        if (strpos($project->handle, 'dev-') === 0) {
            $project->handle = substr($project->handle, 4);
        }

        if (file_exists($project->path . '.alias')) {
            $project->handle = trim(file_get_contents($project->path . '.alias'));
        }

        $project->handle = strtolower($project->handle);

        return $project;
    }

    public function getRepoPath()
    {
        if (!file_exists($file = $this->path . '/.repo')) {
            return $this->path;
        }

        return $this->path . trim(file_get_contents($file)) . '/';
    }

}

