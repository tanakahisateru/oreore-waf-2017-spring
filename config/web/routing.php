<?php
use Aura\Di\Container;
use Aura\Router\Map;
use Aura\Router\RouterContainer;
use My\Web\Lib\View\View;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

/** @var RouterContainer $this */
/** @var Container $di */
/** @var array $params */

$map = $this->getMap();

$map->attach('site.', '', function (Map $map) use ($di) {
    $map->route('index', '/');
    $map->route('contact', '/contact');

    $map->get('privacy', '/privacy')->handler(function () use ($di) {
        $view = $di->get('viewEngine')->createView();
        assert($view instanceof View);
        $view->setFolder('current', 'site');
        return new HtmlResponse($view->render('current::privacy.php'));
    });
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
