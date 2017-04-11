<?php
use Aura\Di\Container;
use Aura\Router\Map;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

/** @var Container $di */
/** @var Map $map */

$map->attach('site.', '', function (Map $map) {
    $map->route('index', '/');
    $map->route('contact', '/contact');
});

$map->attach('api.', '/api', function (Map $map) use ($di) {

    $postsHandler = function (ServerRequestInterface $request) use ($di) {
        $di->get('logger')->debug('api.posts');
        $di->get('logger')->debug(http_build_query($request->getQueryParams()));
        return new JsonResponse(['posts' => []]);
    };

    $map->get('posts', '/posts')->handler($postsHandler);
});
