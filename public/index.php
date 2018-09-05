<?php

use Acme\App\App;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Zend\Stratigility\Middleware\ErrorResponseGenerator;

require __DIR__ . '/../config/bootstrap.php';

$app = App::configure([
    __DIR__ . '/../config/shared',
    __DIR__ . '/../config/web',
], 'di-*.php', require __DIR__ . '/../config/params.php');

$whenServerRequestCreationFailed = function ($e) {
    $isDevelopmentMode = getenv('MY_APP_ENV') == 'dev';
    $generator = new ErrorResponseGenerator($isDevelopmentMode);
    return $generator($e, new ServerRequest(), new Response());
};

$runner = new RequestHandlerRunner(
    $app->getContainer()->get('middlewarePipe'),
    new SapiEmitter(),
    [ServerRequestFactory::class, 'fromGlobals'],
    $whenServerRequestCreationFailed
);

$runner->run();
