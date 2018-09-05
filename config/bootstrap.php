<?php
require __DIR__ . '/../vendor/autoload.php';

// dotenv
$dotenv = new \Symfony\Component\Dotenv\Dotenv();
if (is_file(__DIR__ . '/../.env')) {
    $dotenv->load(__DIR__ . '/../.env');
}
$dotenv->load(__DIR__ . '/default.env');
// ini
// error handler
// polyfills
