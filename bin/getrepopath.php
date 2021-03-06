#!/usr/bin/env php
<?php

require_once __DIR__.'/../vendor/autoload.php';

$ptool = new PTool\PTool();
try {
    if (empty($argv[1])) {
        $project = $ptool->getCurrentProject();
    } else {
        $project = $ptool->getProject($argv[1]);
    }

    echo $project->getRepoPath();
} catch (Exception $e) {
    exit(1);
}
