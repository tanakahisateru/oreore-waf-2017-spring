<?php
require __DIR__ . '/../vendor/autoload.php';

// dotenv
\josegonzalez\Dotenv\Loader::load([
    'filepaths' => [
        __DIR__ . '/../.env',
    ],
    'putenv' => true,
    'toEnv' => true,
]);

// ini
// error handler
// polyfills
