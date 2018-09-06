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
    public function setResponder(Responder $responder)
    {
        $this->responder = $responder;
    }

    ///

    /**
     * @return View
     */
    protected function createViewPrototype()
    {
        return $this->responder->createViewPrototype();
    }

    /**
     * @param string $route
     * @param array $data
     * @param bool $raw
     * @return string
     */
    protected function routeUrlTo($route, $data = [], $raw = false)
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
    protected function contentResponse($content, $contentType, $status = 200, array $headers = [])
    {
        return $this->responder->contentResponse($content, $contentType, $status, $headers);
    }

    /**
     * @param string $html
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    protected function htmlResponse($html, $status = 200, array $headers = [])
    {
        return $this->responder->htmlResponse($html, $status, $headers);
    }

    /**
     * @param string $text
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    protected function textResponse($text, $status = 200, array $headers = [])
    {
        return $this->responder->textResponse($text, $status, $headers);
    }

    /**
     * @param array $json
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    protected function jsonResponse($json, $status = 200, array $headers = [])
    {
        return $this->responder->jsonResponse($json, $status, $headers);
    }

    /**
     * @param string $url
     * @return ResponseInterface
     */
    protected function redirectResponse($url)
    {
        return $this->responder->redirectResponse($url);
    }

    /**
     * @param string $route
     * @param array $data
     * @return ResponseInterface
     */
    protected function redirectResponseToRoute($route, $data = [])
    {
        return $this->responder->redirectResponseToRoute($route, $data);
    }
}
