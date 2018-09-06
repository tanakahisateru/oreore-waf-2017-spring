<?php
namespace Acme\App\Debug\Middleware;

use DebugBar\DebugBar;
use League\Plates\Template\Template;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DebugBarInsertion implements MiddlewareInterface
{
    const PLACEHOLDER_HEAD = "<!-- PLACEHOLDER_DEBUGBAR_HEAD -->";
    const PLACEHOLDER_BODY = "<!-- PLACEHOLDER_DEBUGBAR_BODY -->";

    /**
     * @var DebugBar
     */
    protected $debugbar;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    // TODO Filter by IP or some client identifier not to show internal info.

    /**
     * DebugBarInsertion constructor.
     * @param DebugBar $debugbar
     * @param string $baseUrl
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(DebugBar $debugbar, string $baseUrl, StreamFactoryInterface $streamFactory)
    {
        $this->debugbar = $debugbar;
        $this->baseUrl = $baseUrl;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @param Template $template
     * @param string $headerSlot
     * @param string $bodySlot
     * @param bool $push
     */
    public static function placeholder(Template $template, string $headerSlot, string $bodySlot, bool $push = true): void
    {
        $method = $push ? 'push' : 'start';

        $template->$method($headerSlot);
        echo self::PLACEHOLDER_HEAD . "\n";
        $template->end();

        $template->$method($bodySlot);
        echo self::PLACEHOLDER_BODY . "\n";
        $template->end();
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!$this->isHtmlAccepted($request)) {
            return $response;
        }

        if ($this->isHtmlResponse($response)) {
            $renderer = $this->debugbar->getJavascriptRenderer($this->baseUrl);
            $contents = strval($response->getBody());
            $jsbody = $renderer->render();
            $jshead = $renderer->renderHead();
            $contents = str_replace([
                static::PLACEHOLDER_HEAD,
                static::PLACEHOLDER_BODY,
            ], [
                $jshead,
                $jsbody,
            ], $contents);
            $response = $response->withBody($this->streamFactory->createStream($contents));
        }
        // TODO Wrap content with HTML if debug browser directly accessed.

        return $response;
    }

    private function isHtmlAccepted(ServerRequestInterface $request): bool
    {
        // FIXME Consider X-Requested-With
        return $this->hasHeaderContains($request, 'Accept', 'text/html');
    }

    private function isHtmlResponse(ResponseInterface $response): bool
    {
        return $this->hasHeaderContains($response, 'Content-Type', 'text/html');
    }

    private function hasHeaderContains(MessageInterface $message, string $headerName, string $value): bool
    {
        return strpos($message->getHeaderLine($headerName), $value) !== false;
    }
}
