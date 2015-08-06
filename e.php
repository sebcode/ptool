#!/usr/bin/env php
<?php

$buf = array();
$sel = 0;

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
    $arg = false;

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

    if ($arg === '-a') {
        $arg = false;
    }

    $onlyGit = false;
    if ($arg === '-g') {
        $arg = false;
        $onlyGit = true;
    }

    $path = $project->getRepoPath();
    $cmd = "find ". rtrim($project->getRepoPath(), '/') ." ";
    foreach ($ignoreDirs as &$ignoreDir) {
        $ignoreDir = ' -not \( -name ' . $ignoreDir . ' -prune \) ';
    }
    $cmd .= implode('-and', $ignoreDirs);
    if ($arg) {
        $cmd .= " | grep -i " . escapeshellcmd($arg);
    }

    if ($onlyGit) {
        $output = '';
    } else {
        $output = shell_exec($cmd);

        if (!$output) {
            echo "No results :-(\n";
            exit(1);
        }
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

    $gitfiles = array();
    $res = shell_exec("cd $path; git status --porcelain 2>/dev/null");
    foreach (explode("\n", $res) as $file) {
        if (!$file = substr($file, 3)) {
            continue;
        }
        if ($file = realpath($path .'/'. $file)) {
            $gitfiles[] = $file;
            if ($onlyGit) {
                $output .= "$file\n";
            }
        }
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

        $rel = substr($file, strlen($path));
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
                if ($prioChange === 'exclude') {
                    continue 2;
                }

                $prio += $prioChange;
            }
        }

        /* put direct match to top */
        if ($arg && basename($reli) == strtolower($arg)) {
            $prio += 600;
        }

        if (in_array($file, $gitfiles)) {
            $prio += 500;
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
        $rel = substr($file, strlen($path));
        $prio = $prios[$i];
        $isGitFile = in_array($file, $gitfiles);
        $hasColor = false;

        $line = '';
        $line .= str_pad("($prio)", 6, ' ', STR_PAD_RIGHT);
        $line .= str_pad($i, 3, ' ', STR_PAD_LEFT) . ' ';

        if (in_array($file, $openFiles)) {
            $line .= "\e[0;35m";
            $hasColor = true;
        } else if ($isGitFile) {
            $line .= "\e[0;32m";
            $hasColor = true;
        }

        $line .= " $rel";

        if ($hasColor) {
            $line .= "\e[0m";
        }

        buf($line);
    }

    if ($numMore) {
        buf("($numMore more)\n");
    }

    if (empty($files)) {
        echo "No results :-(\n";
        exit(1);
    }

    buf("\nSelect? ");

    readline_callback_handler_install('', function() { });

    $sel = 0;
    passthru('clear');
    printbuf();

    while (true) {
      $r = array(STDIN);
      $w = NULL;
      $e = NULL;
      $n = stream_select($r, $w, $e, 0);
      if ($n && in_array(STDIN, $r)) {
        passthru('clear');
        $c = stream_get_contents(STDIN, 1);
        if (is_numeric($c)) {
          if (!$sel) {
            $sel = $c;
          } else {
            $sel .= $c;
          }
        }
        if (ord($c) === 8) {
          $sel = 0;
        }
        if (ord($c) === 10) {
          break;
        }
        if (strtolower($c) === 'j') {
          $sel++;
        }
        if (strtolower($c) === 'k') {
          $sel--;
        }
        if ($sel <= 0) {
            $sel = 0;
        }
        printbuf();
      }
    }

    if (!$sel) {
        $sel = 0;
    }

    if (isset($files[$sel])) {
        fwrite(STDERR, $files[$sel]);
        exit(0);
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

function buf($s)
{
    global $buf;
    $buf[] = $s;
}

function printbuf()
{
    global $buf, $sel;
    foreach ($buf as $i => $s) {
        if ($i == $sel) {
            echo ">";
        } else {
            echo " ";
        }
        echo " $s\n";
    }
}

