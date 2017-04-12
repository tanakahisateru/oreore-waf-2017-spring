<?php
use Aura\Di\Container;

/** @var Container $di */

// example:
// $di->set('domain.newsRepository', $di->lazyNew(NewsRepository::class, [
//     'db' => $di->lazyGet('db1'),
// ]));
