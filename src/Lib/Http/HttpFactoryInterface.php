<?php
namespace My\Web\Lib\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

interface HttpFactoryInterface
{
    /**
     * Default flags for json_encode; value of:
     *
     * <code>
     * JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
     * </code>
     *
     * @const int
     */
    const DEFAULT_JSON_FLAGS = 79;

    /**
     * Create a request from the supplied superglobal values.
     *
     * @return ServerRequestInterface
     */
    public function createRequestFromGlobals();

    /**
     * @param string|resource|StreamInterface $body Stream identifier and/or actual stream resource
     * @param int $status Status code for the response, if any.
     * @param array $headers Headers for the response, if any.
     * @return ResponseInterface
     */
    public function createResponse($body = 'php://memory', $status = 200, array $headers = []);


    /**
     * Create an empty response with the given status code.
     *
     * @param int $status Status code for the response, if any.
     * @param array $headers Headers for the response, if any.
     * @return ResponseInterface
     */
    public function createEmptyResponse($status = 204, array $headers = []);

    /**
     * Create a plain text response.
     *
     * Produces a text response with a Content-Type of text/plain and a default
     * status of 200.
     *
     * @param string|StreamInterface $text String or stream for the message body.
     * @param int $status Integer status code for the response; 200 by default.
     * @param array $headers Array of headers to use at initialization.
     * @return ResponseInterface
     */
    public function createTextResponse($text, $status = 200, array $headers = []);

    /**
     * Create an HTML response.
     *
     * Produces an HTML response with a Content-Type of text/html and a default
     * status of 200.
     *
     * @param string|StreamInterface $html HTML or stream for the message body.
     * @param int $status Integer status code for the response; 200 by default.
     * @param array $headers Array of headers to use at initialization.
     * @return ResponseInterface
     */
    public function createHtmlResponse($html, $status = 200, array $headers = []);

    /**
     * Create a JSON response with the given data.
     *
     * Default JSON encoding is performed with the following options, which
     * produces RFC4627-compliant JSON, capable of embedding into HTML.
     *
     * - JSON_HEX_TAG
     * - JSON_HEX_APOS
     * - JSON_HEX_AMP
     * - JSON_HEX_QUOT
     * - JSON_UNESCAPED_SLASHES
     *
     * @param mixed $data Data to convert to JSON.
     * @param int $status Integer status code for the response; 200 by default.
     * @param array $headers Array of headers to use at initialization.
     * @param int $encodingOptions JSON encoding options to use.
     * @return ResponseInterface
     */
    public function createJsonResponse(
        $data,
        $status = 200,
        array $headers = [],
        $encodingOptions = self::DEFAULT_JSON_FLAGS
    );

    /**
     * Create a redirect response.
     *
     * Produces a redirect response with a Location header and the given status
     * (302 by default).
     *
     * Note: this method overwrites the `location` $headers value.
     *
     * @param string|UriInterface $uri URI for the Location header.
     * @param int $status Integer status code for the redirect; 302 by default.
     * @param array $headers Array of headers to use at initialization.
     * @return ResponseInterface
     */
    public function createRedirectResponse($uri, $status = 302, array $headers = []);

    /**
     * @param string|resource $stream
     * @param string $mode Mode with which to open stream
     * @return StreamInterface
     */
    public function createStream($stream, $mode = 'r');

    /**
     * @param string $uri
     * @return UriInterface
     */
    public function createUri($uri = '');
}
