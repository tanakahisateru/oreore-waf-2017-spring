<?php
namespace Acme\App\Responder;

use Acme\App\Router\NoSuchRouteException;
use Acme\App\Router\Router;
use Acme\App\View\View;
use Acme\App\View\ViewFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class Responder
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var ViewFactory
     */
    protected $viewFactory;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * ControllerManager constructor.
     * @param ViewFactory $viewFactory
     * @param Router $router
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(
        ViewFactory $viewFactory,
        Router $router,
        ResponseFactoryInterface $responseFactory
    )
    {
        $this->router = $router;
        $this->viewFactory = $viewFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @return View
     */
    public function createViewPrototype(): View
    {
        return $this->viewFactory->createView($this->router);
    }

    /**
     * @param string $route
     * @param array $data
     * @param bool $raw
     * @return string
     * @throws NoSuchRouteException
     */
    public function routeUrlTo(string $route, array $data = [], bool $raw = false): string
    {
        return $this->router->uriTo($route, $data, $raw);
    }

    /**
     * @param string $content
     * @param string $contentType
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function contentResponse(string $content, string $contentType, int $status = 200, array $headers = []): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($status)
            ->withHeader('Content-Type', $contentType);

        foreach ($headers as $name => $header) {
            $response = $response->withHeader($name, $header);
        }

        $response->getBody()->write($content);

        return $response;
    }

    /**
     * @param string $html
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function htmlResponse(string $html, int $status = 200, array $headers = []): ResponseInterface
    {
        return $this->contentResponse($html, 'text/html; charset=utf-8', $status, $headers);
    }

    /**
     * @param string $text
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function textResponse(string $text, int $status = 200, array $headers = []): ResponseInterface
    {
        return $this->contentResponse($text, 'text/plain; charset=utf-8', $status, $headers);
    }

    /**
     * @param array $json
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function jsonResponse(array $json, int $status = 200, array $headers = []): ResponseInterface
    {
        $jsonText = json_encode($json);
        return $this->contentResponse($jsonText, 'application/json; charset=utf-8', $status, $headers);
    }

    /**
     * @param string $url
     * @return ResponseInterface
     */
    public function redirectResponse(string $url): ResponseInterface
    {
        return $this->responseFactory->createResponse(302)->withHeader('Location', $url);
    }

    /**
     * @param string $route
     * @param array $data
     * @return ResponseInterface
     */
    public function redirectResponseToRoute(string $route, array $data = []): ResponseInterface
    {
        return $this->redirectResponse($this->routeUrlTo($route, $data));
    }
}
