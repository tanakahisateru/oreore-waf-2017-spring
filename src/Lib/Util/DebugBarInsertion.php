<?php
namespace My\Web\Lib\Util;

use DebugBar\DebugBar;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use League\Plates\Template\Template;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    // TODO Filter by IP or some client identifier not to show internal info.

    /**
     * DebugBarInsertion constructor.
     * @param DebugBar $debugbar
     * @param string $baseUrl
     */
    public function __construct(DebugBar $debugbar, $baseUrl)
    {
        $this->debugbar = $debugbar;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param Template $template
     * @param string $headerSlot
     * @param string $bodySlot
     * @param bool $push
     */
    public static function placeholder($template, $headerSlot, $bodySlot, $push = true)
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
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $response = $delegate->process($request);

        if (!$this->isHtmlAccepted($request)) {
            return $response;
        }

        if ($this->isHtmlResponse($response)) {
            $renderer = $this->debugbar->getJavascriptRenderer($this->baseUrl);
            $body = $response->getBody();
            $jsbody = $renderer->render();
            $jshead = $renderer->renderHead();
            if (!$body->eof() && $body->isSeekable()) {
                $body->rewind();
                $contents = $body->getContents();
                $body->rewind();
                $body->write(str_replace([
                    static::PLACEHOLDER_HEAD,
                    static::PLACEHOLDER_BODY,
                ], [
                    $jshead,
                    $jsbody,
                ], $contents));
            } else {
                $body->write($jshead . $jsbody);
            }
        } else {
            // TODO Wrap content with HTML if debug browser directly accessed.
        }

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    private function isHtmlAccepted(ServerRequestInterface $request)
    {
        // FIXME Consider X-Requested-With
        return $this->hasHeaderContains($request, 'Accept', 'text/html');
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    private function isHtmlResponse(ResponseInterface $response)
    {
        return $this->hasHeaderContains($response, 'Content-Type', 'text/html');
    }

    /**
     * @param MessageInterface $message
     * @param string $headerName
     * @param string $value
     *
     * @return bool
     */
    private function hasHeaderContains(MessageInterface $message, $headerName, $value)
    {
        return strpos($message->getHeaderLine($headerName), $value) !== false;
    }
}
