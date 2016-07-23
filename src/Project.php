<?php

namespace PTool;

class Project
{
    public $handle;
    public $path;
    public $desc;

    public static function createFromPath($path)
    {
        $project = new self();
        $project->handle = basename($path);
        $project->path = $path;

        if (file_exists($file = $project->path.'.alias')) {
            $c = file($file);
            $project->handle = trim($c[0]);
            if (!empty($c[1])) {
                $project->desc = trim($c[1]);
            }
        }

        $project->handle = strtolower($project->handle);

        return $project;
    }

    public function getRepoPaths()
    {
        if (!file_exists($file = $this->path.'/.repo')) {
            return [$this->path];
        }

        $c = file($file);

        $result = [];

        foreach (file($file) as $subdir) {
            $result[] = $this->path.trim(trim($subdir), '/').'/';
        }

        return $result;
    }

    public function getRepoPath()
    {
        $ret = $this->getRepoPaths();

        return reset($ret);
    }
}
