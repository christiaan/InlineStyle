<?php
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoloader)) {
    echo 'Autoloader not found. Did you forget to run composer install?' . PHP_EOL;
    exit(1);
}
require $autoloader;