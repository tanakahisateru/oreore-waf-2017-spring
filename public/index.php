<?php

use Acme\App\App;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Zend\Stratigility\Middleware\ErrorResponseGenerator;

require __DIR__ . '/../config/bootstrap.php';

$app = App::configure([
    __DIR__ . '/../config/shared',
    __DIR__ . '/../config/web',
], 'di-*.php', require __DIR__ . '/../config/params.php');


$runner = new RequestHandlerRunner(
    $app->getContainer()->get('middlewarePipe'),
    new SapiEmitter(),
    [ServerRequestFactory::class, 'fromGlobals'],
    function ($e) {
        $generator = new ErrorResponseGenerator();
        return $generator($e, ServerRequestFactory::fromGlobals(), new Response());
    }
);
$runner->run();
