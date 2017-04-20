<?php
use Aura\Di\Container;
use My\Web\Lib\View\Asset\AssetManager;

/** @var Container $di */
/** @var AssetManager $am */

$am->set('jquery', $am->asset([
    'baseUrl' => '/assets/vendor/jquery/dist',
    'elements' => ['jquery.js'],
    'stage' => 'before-end-body-script',
]));

$am->set('bootstrap', $am->asset([
    'baseUrl' => '/assets/vendor/bootstrap/dist',
    'subset' => [
        [
            'elements' => ['css/bootstrap.css', 'css/bootstrap-theme.css'],
            'stage' => 'before-end-head-css',
        ],
        [
            'elements' => ['js/bootstrap.js'],
            'stage' => 'before-end-body-script',
        ],
    ],
    'dependencies' => ['jquery'],
]));

$am->set('app', $am->asset([
    'baseUrl' => '/assets/local',
    'subset' => [
        [
            'elements' => ['app.css'],
            'stage' => 'before-end-head-css',
        ],
        [
            'elements' => ['app.js'],
            'stage' => 'before-end-body-script',
        ],
    ],
    'dependencies' => ['jquery', 'bootstrap'],
]));

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

    $revManifestPath = __DIR__ . '/../../web/assets/dist/rev-manifest.json';
    if (is_file($revManifestPath)) {
        $am->rev(json_decode(file_get_contents($revManifestPath)), '/assets/dist/');
    }
}
