<?php
namespace My\Web;

use My\Web\Lib\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class WebApp extends App
{
    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->getService('router', Router::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $response)
    {
        //try {
        $this->getLogger()->debug("Request handling started");
        $startedAt = microtime(true);

        // filter

        ob_start();
        $responseBeforeDispatch = $response;

        $response = $this->getRouter()->dispatch($request, $response);

        if (empty($response)) {
            $response = $responseBeforeDispatch;
            $response->getBody()->write(ob_get_clean());
        } elseif (is_array($response)) {
            $response = $responseBeforeDispatch->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($response));
            ob_end_clean();
        } elseif (is_scalar($response)) {
            $response = $responseBeforeDispatch;
            $response->getBody()->write($response);
            ob_end_clean();
        } elseif ($response instanceof ResponseInterface) {
            $echo = ob_get_clean();
            if (!empty($echo)) {
                $stream = $response->getBody();
                $body = $stream->getContents();
                $stream->rewind();
                $stream->write($body . $echo);
            }
        } else {
            throw new \LogicException('Invalid response returned on: ' . $request->getUri());
        }

        // if response: filter
        $elapsed = microtime(true) - $startedAt;
        $this->getLogger()->debug(sprintf("Request handling finished in %0.3fms", $elapsed * 1000));

        //} catch (\Exception)

        return $response;
    }

    /**
     *
     */
    public function run()
    {
        $request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

        $response = $this->handle($request, new Response());

        if ($response->getStatusCode() != 200) {
            @header(sprintf('HTTP/%s %d %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));
        }

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                @header(sprintf('%s: %s', $name, $value), false);
            }
        }

        echo $response->getBody();
    }
}
