#!/usr/bin/env php
<?php

require_once __DIR__.'/autoloader.php';

$ptool = new PTool\PTool();
try {
    if (empty($argv[1])) {
        $project = $ptool->getCurrentProject();
    } else {
        $project = $ptool->getProject($argv[1]);
    }

    echo $project->path;
} catch (Exception $e) {
    exit(1);
}
