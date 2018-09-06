<?php
namespace Acme\App\Responder;

use Acme\App\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

trait ResponderAwareTrait
{
    /**
     * @var Responder
     */
    protected $responder;

    /**
     * @param Responder $responder
     */
    public function setResponder(Responder $responder): void
    {
        $this->responder = $responder;
    }

    ///

    /**
     * @return View
     */
    protected function createViewPrototype(): View
    {
        return $this->responder->createViewPrototype();
    }

    /**
     * @param string $route
     * @param array $data
     * @param bool $raw
     * @return string
     */
    protected function routeUrlTo(string $route, array $data = [], $raw = false): string
    {
        return $this->responder->routeUrlTo($route, $data, $raw);
    }

    /**
     * @param string|StreamInterface $content
     * @param string $contentType
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    protected function contentResponse(string $content, string $contentType, int $status = 200, array $headers = []): ResponseInterface
    {
        return $this->responder->contentResponse($content, $contentType, $status, $headers);
    }

    /**
     * @param string $html
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    protected function htmlResponse(string $html, int $status = 200, array $headers = []): ResponseInterface
    {
        return $this->responder->htmlResponse($html, $status, $headers);
    }

    /**
     * @param string $text
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    protected function textResponse(string $text, int $status = 200, array $headers = []): ResponseInterface
    {
        return $this->responder->textResponse($text, $status, $headers);
    }

    /**
     * @param array $json
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    protected function jsonResponse(array $json, int $status = 200, array $headers = []): ResponseInterface
    {
        return $this->responder->jsonResponse($json, $status, $headers);
    }

    /**
     * @param string $url
     * @return ResponseInterface
     */
    protected function redirectResponse(string $url): ResponseInterface
    {
        return $this->responder->redirectResponse($url);
    }

    /**
     * @param string $route
     * @param array $data
     * @return ResponseInterface
     */
    protected function redirectResponseToRoute(string $route, array $data = []): ResponseInterface
    {
        return $this->responder->redirectResponseToRoute($route, $data);
    }
}
