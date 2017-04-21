<?php
use Aura\Di\Container;
use My\Web\Lib\Http\DiactorosHttpFactory;
use My\Web\Lib\Http\HttpFactoryAwareInterface;
use My\Web\Lib\Http\WhoopsResponseGenerator;
use My\Web\Lib\Util\PlainPhp;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\Middleware\ErrorHandler;
use Zend\Stratigility\Middleware\ErrorResponseGenerator;
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
    $errorResponseGenerator = getenv('MY_APP_ENV') == 'dev' ?
        new WhoopsResponseGenerator() :
        new ErrorResponseGenerator(false);

    $errorHandler = new ErrorHandler(
        $di->get('httpFactory')->createResponse(),
        $errorResponseGenerator
    );

    $errorHandler->attachListener(function ($error, ServerRequestInterface $request) use ($di) {
        /** @var Throwable|Exception $error */
        $logger = $di->get('logger');
        $logger->error(sprintf("%s(\"%s\") - %s", get_class($error), $error->getMessage(), $request->getUri()));
        foreach (explode("\n", $error->getTraceAsString()) as $trace) {
            $logger->error($trace);
        }
    });

    return $errorHandler;
}));
