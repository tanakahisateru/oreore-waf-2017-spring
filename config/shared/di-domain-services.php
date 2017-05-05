<?php
use My\Web\Lib\Container\Container;

/** @var Container $di */
/** @var array $params */

// example:
// $di->set('domain.newsRepository', $di->lazyNew(NewsRepository::class, [
//     'db' => $di->lazyGet('db1'),
// ]));
