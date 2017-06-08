<?php
namespace Acme\App\Presentation;

use Acme\App\View\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

trait PresentationHelperAwareTrait
{
    /**
     * @var PresentationHelper
     */
    protected $presentationHelper;

    /**
     * @param PresentationHelper $responseAgent
     */
    public function setPresentationHelper(PresentationHelper $responseAgent)
    {
        $this->presentationHelper = $responseAgent;
    }

    ///

    /**
     * @return View
     */
    protected function createViewPrototype()
    {
        return $this->presentationHelper->createViewPrototype();
    }

    /**
     * @param string $content
     * @return StreamInterface
     */
    protected function createStream($content = "")
    {
        return $this->presentationHelper->createStream($content);
    }

    /**
     * @param string $route
     * @param array $data
     * @param bool $raw
     * @return string
     */
    protected function routeUrlTo($route, $data = [], $raw = false)
    {
        return $this->presentationHelper->routeUrlTo($route, $data, $raw);
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
        return $this->presentationHelper->contentResponse($content, $contentType, $status, $headers);
    }

    /**
     * @param string $html
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    protected function htmlResponse($html, $status = 200, array $headers = [])
    {
        return $this->presentationHelper->htmlResponse($html, $status, $headers);
    }

    /**
     * @param string $text
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    protected function textResponse($text, $status = 200, array $headers = [])
    {
        return $this->presentationHelper->textResponse($text, $status, $headers);
    }

    /**
     * @param array $json
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    protected function jsonResponse($json, $status = 200, array $headers = [])
    {
        return $this->presentationHelper->jsonResponse($json, $status, $headers);
    }

    /**
     * @param string $url
     * @return ResponseInterface
     */
    protected function redirectResponse($url)
    {
        return $this->presentationHelper->redirectResponse($url);
    }

    /**
     * @param string $route
     * @param array $data
     * @return ResponseInterface
     */
    protected function redirectResponseToRoute($route, $data = [])
    {
        return $this->presentationHelper->redirectResponseToRoute($route, $data);
    }
}
