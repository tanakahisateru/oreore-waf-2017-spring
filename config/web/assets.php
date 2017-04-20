<?php
use Aura\Di\Container;
use My\Web\Lib\View\Asset\AssetManager;

/** @var Container $di */
/** @var AssetManager $am */

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

//////////////////////////////////////////////////////////
$allCssMapPath = __DIR__ . '/../../web/assets/dist/css/all.min.css.map';
$allJsMapPath = __DIR__ . '/../../web/assets/dist/js/all.min.js.map';
$revManifestPath = __DIR__ . '/../../web/assets/dist/rev-manifest.json';

if (is_file($allCssMapPath)) {
    $sources = json_decode(file_get_contents($allCssMapPath), true)['sources'];
    $am->map('/assets/', 'dist/css/all.min.css', $sources);
}
if (is_file($allJsMapPath)) {
    $sources = json_decode(file_get_contents($allJsMapPath), true)['sources'];
    $am->map('/assets/', 'dist/js/all.min.js', $sources);
}
if (is_file($revManifestPath)) {
    $manifest = json_decode(file_get_contents($revManifestPath), true);
    $am->rev('/assets/dist/', $manifest);
}
