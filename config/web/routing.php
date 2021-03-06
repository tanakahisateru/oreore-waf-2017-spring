<?php

use Acme\App\Router\Router;
use Acme\App\View\ViewFactory;
use Aura\Di\Container;
use Aura\Router\Map;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

/** @var Router $this */
/** @var Container $di */
/** @var array $params */

$map = $this->getRoutes()->getMap();

$map->attach('site.', '', function (Map $map) use ($di) {
    $map->route('index', '/');
    $map->route('contact', '/contact');

    $map->get('privacy', '/privacy')->handler(function () use ($di) {
        $viewFactory = $di->get(ViewFactory::class);
        assert($viewFactory instanceof ViewFactory);
        $view = $viewFactory->createView($di->get(Router::class));
        $view->setFolder('current', 'site');
        return new HtmlResponse($view->render('current::privacy.php'));
    });

    $map->route('redirect', '/redirect');
    $map->route('notFound', '/not-found');
    $map->route('forbidden', '/forbidden');
});

$map->attach('api.', '/api', function (Map $map) use ($di) {

    $postsHandler = function (ServerRequestInterface $request) use ($di) {
        $di->get('logger')->debug('api.posts');
        $di->get('logger')->debug(http_build_query($request->getQueryParams()));
        return $di->newInstance(JsonResponse::class, [
            'data' => [
                'posts' => []
            ],
        ]);
    };

    $map->get('posts', '/posts')->handler($postsHandler);
});
