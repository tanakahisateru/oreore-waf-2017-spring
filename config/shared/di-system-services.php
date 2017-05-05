<?php
use My\Web\Lib\Container\Container;

/** @var Container $di */
/** @var array $params */

$di->set('db1', $di->lazyNew(PDO::class, [
    'dsn' => getenv('MY_APP_DB_DSN'),
    'username' => getenv('MY_APP_DB_USERNAME'),
    'passwd' => getenv('MY_APP_DB_PASSWORD'),
    'options' => [],
]));
