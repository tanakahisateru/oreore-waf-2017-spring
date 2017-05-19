<?php
use Lapaz\Amechan\AssetManager;
use Lapaz\Amechan\Mapper\UnifiedResourceMapper;
use My\Web\Lib\Container\Container;

/** @var Container $di */
/** @var AssetManager $am */

$am->asset('jquery', [
    'file' => '/assets/vendor/jquery/dist/jquery.js',
    'section' => 'before-end-body-script',
]);

$am->asset('bootstrap', [
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

$am->asset('app', [
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
        __DIR__ . '/../../web/assets/dist/css/all.min.css.map',
    'dist/js/all.min.js' =>
        __DIR__ . '/../../web/assets/dist/js/all.min.js.map',
];

$revManifestPath = __DIR__ . '/../../web/assets/dist/rev-manifest.json';

$mapping = array_combine(
    array_keys($pathMapping),
    array_map(function ($mapPath) {
        return is_file($mapPath) ?
            json_decode(file_get_contents($mapPath), true)['sources'] : [];
    }, array_values($pathMapping))
);

$am->mapping($di->newInstance(UnifiedResourceMapper::class, [
    'baseUrl' => '/assets/',
    'mapping' => $mapping,
]));

$manifest = is_file($revManifestPath) ?
    json_decode(file_get_contents($revManifestPath), true) : [];
$am->mapping($di->newInstance(\Lapaz\Amechan\Mapper\RevisionHashMapper::class, [
    'baseUrl' => '/assets/dist/',
    'manifest' => $manifest,

]));
