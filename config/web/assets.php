<?php
use Aura\Di\Container;
use My\Web\Lib\View\Asset\AssetInterface;
use My\Web\Lib\View\Asset\AssetManager;
use My\Web\Lib\View\Asset\LocalFileAsset;

/** @var Container $di */
/** @var AssetManager $am */

/** @var AssetInterface[] $assets */

$am->registerAsset($di->newInstance(LocalFileAsset::class, [
    'name' => 'jquery',
    'baseUrl' => '/assets/jquery/dist',
    'elements' => [
        'jquery.js',
    ],
    'stage' => 'before-end-body-script',
]));

$am->registerAsset($di->newInstance(LocalFileAsset::class, [
    'name' => 'bootstrap',
    'baseUrl' => null,
    'elements' => [],
    'stage' => null,
    'dependencies' => [
        $di->newInstance(LocalFileAsset::class, [
            'name' => 'bootstrap-css',
            'baseUrl' => '/assets/bootstrap/dist/css',
            'elements' => [
                'bootstrap.css',
                'bootstrap-theme.css',
            ],
            'stage' => 'before-end-head-css',
        ]),
        $di->newInstance(LocalFileAsset::class, [
            'name' => 'bootstrap-js',
            'baseUrl' => '/assets/bootstrap/dist/js',
            'elements' => [
                'bootstrap.js',
            ],
            'stage' => 'before-end-body-script',
            'dependencies' => [
                $am->getAsset('jquery'),
            ]
        ]),
    ]
]));
