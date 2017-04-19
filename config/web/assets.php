<?php
use Aura\Di\Container;
use My\Web\Lib\View\Asset\AssetInterface;
use My\Web\Lib\View\Asset\AssetManager;

/** @var Container $di */
/** @var AssetManager $am */

/** @var AssetInterface[] $assets */

$am->register('jquery', $am->newAsset([
    'baseUrl' => '/assets/jquery/dist',
    'elements' => ['jquery.js'],
    'stage' => 'before-end-body-script',
]));

$am->register('bootstrap', $am->newAsset([
    'baseUrl' => '/assets/bootstrap/dist',
    'dependencies' => [
        [
            'elements' => ['css/bootstrap.css', 'css/bootstrap-theme.css'],
            'stage' => 'before-end-head-css',
        ],
        [
            'elements' => ['js/bootstrap.js'],
            'stage' => 'before-end-body-script',
            'dependencies' => ['jquery']
        ],
    ]
]));
