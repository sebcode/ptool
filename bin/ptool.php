#!/usr/bin/env php
<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    $app = new \PTool\App();
    $app->process($argv);
    exit(0);
} catch (\Exception $e) {
    fwrite(STDERR, 'Error: '.$e->getMessage()."\n");
    exit(1);
}
