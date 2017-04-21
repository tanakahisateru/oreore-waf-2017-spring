<?php
use Aura\Di\Container;
use My\Web\Lib\Http\DiactorosHttpFactory;
use My\Web\Lib\Http\HttpFactoryAwareInterface;
use My\Web\Lib\Http\Middleware\WhoopsErrorResponseGenerator;
use My\Web\Lib\Router\Router;
use My\Web\Lib\Util\PlainPhp;
use My\Web\Lib\View\Middleware\ErrorResponseGenerator;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\Middleware\ErrorHandler;
use Zend\Stratigility\MiddlewarePipe;

/** @var Container $di */

$di->setters[HttpFactoryAwareInterface::class] = [
    'setHttpFactory' => $di->lazyGet('httpFactory'),
];

$di->set('httpFactory', $di->lazyNew(DiactorosHttpFactory::class));

$di->set('middlewarePipe', $di->lazy(function () use ($di) {
    $pipe = new MiddlewarePipe();
    PlainPhp::runner()->with([
        'di' => $di,
        'pipe' => $pipe,
    ])->doRequire(__DIR__ . '/middleware.php');
    return $pipe;
}));

$di->set('errorHandlerMiddleware', $di->lazy(function () use ($di) {
    /** @var Router $router */
    $router = $di->get('router');
    $errorResponseGenerator = getenv('MY_APP_ENV') == 'dev' ?
        new WhoopsErrorResponseGenerator() :
        new ErrorResponseGenerator($router);

    $errorHandler = new ErrorHandler(
        $di->get('httpFactory')->createResponse(),
        $errorResponseGenerator
    );

    $errorHandler->attachListener(function ($error, ServerRequestInterface $request) use ($di) {
        /** @var Exception|mixed $error */
        $logger = $di->get('logger');
        $logger->error(sprintf("%s(\"%s\") - %s", get_class($error), $error->getMessage(), $request->getUri()));
        foreach (explode("\n", $error->getTraceAsString()) as $trace) {
            $logger->error($trace);
        }
    });

    return $errorHandler;
}));
