#!/usr/bin/env php
<?php

if (file_exists($f = $_SERVER['HOME'] . '/.ptoolignore')) {
    $ignoreDirs = include($f);
} else {
    $ignoreDirs = array(
        '.git',
        '.svn',
    );
}

require_once(__DIR__ . '/autoloader.php');

try {
    $ptool = new PTool\PTool;

    if ($argc <= 1) {
        $arg = false;
    }

    $searchExt = '';

    foreach ($argv as $i => $a) {
        if ($i == 0) {
            continue;
        }

        if (strpos($a, '.') === 0) {
            $searchExt = trim($a, '.');
            continue;
        }

        $arg = $a;
    }

    $project = $ptool->getCurrentProject();

    if (!$arg) {
        fwrite(STDERR, $project->getRepoPath());
        exit(0);
    }

    if ($arg === '_NOTES_') {
        foreach (array($project->path . '/todo.txt', $project->path . '/notes.txt') as $file) {
            if (file_exists($file)) {
                fwrite(STDERR, $file);
                exit(0);
            }
        }
        exit(1);
    }

    if ($arg === '-i') {
        echo "selected project path: " . $project->path . "\n";
        exit(0);
    }

    $path = $project->getRepoPath();
    $cmd = "find ". $project->getRepoPath() ." ";
    foreach ($ignoreDirs as &$ignoreDir) {
        $ignoreDir = ' -not \( -name ' . $ignoreDir . ' -prune \) ';
    }
    $cmd .= implode('-and', $ignoreDirs);
    $cmd .= " | grep -i " . escapeshellcmd($arg);

    $output = shell_exec($cmd);

    if (!$output) {
        echo "No results :-(\n";
        exit(1);
    }

    $openFiles = array();
    $files = array();
    $prios = array();

    $i = 0;

    $prioConf = array();
    if (file_exists($f = $project->path . 'e.conf.php')) {
        $prioConf = include($f);
    } else if ($f = findPrioConf('e.conf.php')) {
        $prioConf = include($f);
    }

    foreach (explode("\n", $output) as $file) {
        if (preg_match('@(.*?).swp$@', $file, $m)) {
            $realFile = dirname($m[1]) .'/'. ltrim(basename($m[1]), '.');
            $openFiles[] = $realFile;
            continue;
        }

        if (!file_exists($file) || is_dir($file)) {
            continue;
        }

        $rel = substr($file, strlen($path) + 1);
        $reli = strtolower($rel);
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        $prio = 0;

        if ($searchExt) {
            if ($ext === $searchExt) { $prio += 500; };
            if (strpos($ext, $searchExt) === 0) {
                $prio += 400;
            }
        }

        foreach ($prioConf as $regex => $prioChange) {
            if (preg_match("@$regex@", $rel)) {
                $prio += $prioChange;
            }
        }

        /* put direct match to top */
        if (basename($reli) == strtolower($arg)) {
            $prio += 600;
        }

        $files[$i] = $file;
        $prios[$i] = $prio;

        $i++;
    }

    array_multisort($prios, SORT_DESC, $files);

    $numMore = 0;

    if (count($files) >= 30) {
        $numMore = count($files) - 30;
        $files = array_slice($files, 0, 30, true);
    }

    foreach ($files as $i => $file) {
        $rel = substr($file, strlen($path) + 1);
        $prio = $prios[$i];

        echo str_pad($i, 3, ' ', STR_PAD_LEFT) . ' ';
        echo str_pad("($prio)", 6, ' ', STR_PAD_RIGHT);
        echo " $rel";

        if (in_array($file, $openFiles)) {
            echo " (OPEN)";
        }

        echo "\n";
    }

    if ($numMore) {
        echo "($numMore more)\n";
    }

    if (empty($files)) {
        echo "No results :-(\n";
        exit(1);
    }

    while (true) {
        $sel = trim(readline('Select? '));

        if (!$sel) {
            $sel = 0;
        }

        if (isset($files[$sel])) {
            fwrite(STDERR, $files[$sel]);
            exit(0);
        }

    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);

function findPrioConf($filename)
{
    $d = getcwd();

    while (strlen($d) >= 2) {
        if (file_exists($f = $d .'/'. $filename)) {
            return $f;
        }

        $d = dirname($d);
    }

    return false;
}

