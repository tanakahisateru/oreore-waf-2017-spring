<?php
use Aura\Di\Container;
use My\Web\Lib\View\Asset\AssetManager;

/** @var Container $di */
/** @var AssetManager $am */

$revManifestPath = __DIR__ . '/../../web/assets/dist/rev-manifest.json';

$am->asset('jquery', [
    'file' => '/assets/vendor/jquery/dist/jquery.js',
    'stage' => 'before-end-body-script',
]);

$am->asset('bootstrap', [
    'baseUrl' => '/assets/vendor/bootstrap/dist',
    'bundles' => [
        [
            'files' => ['css/bootstrap.css', 'css/bootstrap-theme.css'],
            'stage' => 'before-end-head-css',
        ],
        [
            'file' => 'js/bootstrap.js',
            'stage' => 'before-end-body-script',
        ],
    ],
    'dependency' => 'jquery',
]);

$am->asset('app', [
    'baseUrl' => '/assets/local',
    'bundles' => [
        [
            'file' => 'app.css',
            'stage' => 'before-end-head-css',
        ],
        [
            'file' => 'app.js',
            'stage' => 'before-end-body-script',
        ],
    ],
    'dependencies' => ['jquery', 'bootstrap'],
]);

if (is_dir(__DIR__ . '/../../web/assets/dist')) {
    $am->map('dist/css/all.min.css', [
        'vendor/bootstrap/dist/css/bootstrap.css',
        'vendor/bootstrap/dist/css/bootstrap-theme.css',
        'local/app.css',
    ], '/assets/');

    $am->map('dist/js/all.min.js', [
        'vendor/jquery/dist/jquery.js',
        'vendor/bootstrap/dist/js/bootstrap.js',
        'local/app.js',
    ], '/assets/');
}

if (is_file($revManifestPath)) {
    $am->rev(json_decode(file_get_contents($revManifestPath)), '/assets/dist/');
}
