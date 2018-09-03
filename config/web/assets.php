<?php
use Aura\Di\Container;
use Lapaz\Amechan\AssetManager;
use Lapaz\Amechan\Mapper\RevisionHashMapper;
use Lapaz\Amechan\Mapper\UnifiedResourceMapper;

/** @var AssetManager $this */
/** @var Container $di */
/** @var array $params */

$this->asset('jquery', [
    'file' => '/assets/vendor/jquery/dist/jquery.js',
    'section' => 'before-end-body-script',
]);

$this->asset('bootstrap', [
    'baseUrl' => '/assets/vendor/bootstrap/dist',
    'bundles' => [
        [
            'files' => ['css/bootstrap.css', 'css/bootstrap-theme.css'],
            'section' => 'before-end-head-css',
        ],
        [
            'file' => 'js/bootstrap.js',
            'section' => 'before-end-body-script',
        ],
    ],
    'dependency' => 'jquery',
]);

$this->asset('app', [
    'baseUrl' => '/assets/local',
    'bundles' => [
        [
            'file' => 'app.css',
            'section' => 'before-end-head-css',
        ],
        [
            'file' => 'app.js',
            'section' => 'before-end-body-script',
        ],
    ],
    'dependencies' => ['jquery', 'bootstrap'],
]);

//////////////////////////////////////////////////////////
$pathMapping = [
    'dist/css/all.min.css' =>
        __DIR__ . '/../../public/assets/dist/css/all.min.css.map',
    'dist/js/all.min.js' =>
        __DIR__ . '/../../public/assets/dist/js/all.min.js.map',
];

$revManifestPath = __DIR__ . '/../../public/assets/dist/rev-manifest.json';

$mapping = array_combine(
    array_keys($pathMapping),
    array_map(function ($mapPath) {
        return is_file($mapPath) ?
            json_decode(file_get_contents($mapPath), true)['sources'] : [];
    }, array_values($pathMapping))
);

$this->mapping(new UnifiedResourceMapper('/assets/', $mapping));

$manifest = is_file($revManifestPath) ?
    json_decode(file_get_contents($revManifestPath), true) : [];

$this->mapping(new RevisionHashMapper('/assets/dist/', $manifest));
