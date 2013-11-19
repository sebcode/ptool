#!/usr/bin/env php
<?php

require_once(__DIR__ . '/autoloader.php');

try {
    $app = new App();
    $app->process($argv);
} catch (Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit (1);
}

