<?php
use Aura\Di\Container;
use Aura\Sql\ExtendedPdo;

/** @var Container $di */
/** @var array $params */

$di->set('db1', $di->lazyNew(ExtendedPdo::class, [
    'dsn' => getenv('MY_APP_DB_DSN'),
    'username' => getenv('MY_APP_DB_USERNAME'),
    'password' => getenv('MY_APP_DB_PASSWORD'),
]));
