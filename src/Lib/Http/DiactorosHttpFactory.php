<?php
namespace My\Web\Lib\Http;

use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream;
use Zend\Diactoros\Uri;

class DiactorosHttpFactory implements HttpFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createRequestFromGlobals()
    {
        return ServerRequestFactory::fromGlobals();
    }

    /**
     * {@inheritDoc}
     */
    public function createResponse($body = 'php://memory', $status = 200, array $headers = [])
    {
        return new Response($body, $status, $headers);
    }


    /**
     * {@inheritDoc}
     */
    public function createEmptyResponse($status = 204, array $headers = [])
    {
        return new Response\EmptyResponse($status, $headers);

    }

    /**
     * {@inheritDoc}
     */
    public function createTextResponse($text, $status = 200, array $headers = [])
    {
        return new Response\TextResponse($text, $status, $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function createHtmlResponse($html, $status = 200, array $headers = [])
    {
        return new Response\HtmlResponse($html, $status, $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function createJsonResponse(
        $data,
        $status = 200,
        array $headers = [],
        $encodingOptions = Response\JsonResponse::DEFAULT_JSON_FLAGS
    )
    {
        return new Response\JsonResponse($data, $status, $headers, $encodingOptions);
    }

    /**
     * {@inheritDoc}
     */
    public function createRedirectResponse($uri, $status = 302, array $headers = [])
    {
        return new Response\RedirectResponse($uri, $status, $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function createStream($stream, $mode = 'r')
    {
        return new Stream($stream, $mode);
    }

    /**
     * {@inheritDoc}
     */
    public function createUri($uri = '')
    {
        return new Uri($uri);
    }
}
