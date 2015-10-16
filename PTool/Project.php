<?php

namespace PTool;

class Project {

    public $handle;
    public $path;

    public static function createFromPath($path) {
        $project = new Project;
        $project->handle = basename($path);
        $project->path = $path;

        if (file_exists($project->path . '.alias')) {
            $project->handle = trim(file_get_contents($project->path . '.alias'));
        }

        $project->handle = strtolower($project->handle);

        return $project;
    }

    public function getRepoPaths() {
        if (!file_exists($file = $this->path . '/.repo')) {
            return [ $this->path ];
        }

        $c = file($file);

        $result = [];

        foreach (file($file) as $subdir) {
            $result[] = $this->path . trim(trim($subdir), '/') . '/';
        }

        return $result;
    }

    public function getRepoPath() {
        $ret = $this->getRepoPaths();
        return reset($ret);
    }

}

