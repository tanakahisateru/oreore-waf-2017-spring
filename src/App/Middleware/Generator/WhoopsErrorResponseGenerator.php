<?php
namespace Acme\App\Middleware\Generator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Util\Misc;
use Zend\Stratigility\Utils;

class WhoopsErrorResponseGenerator
{
    /**
     * Create/update the response representing the error.
     *
     * @param \Exception|mixed $e
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function __invoke($e, ServerRequestInterface $request, ResponseInterface $response)
    {
        $run = new Run();
        $run->writeToOutput(false);
        $run->allowQuit(false);

        $pageHandler = new PrettyPageHandler();
        $pageHandler->addDataTable('PSR-7 Request Attributes', $request->getAttributes());
        $run->pushHandler($pageHandler);

        if (Misc::isAjaxRequest()) {
            $jsonHandler = new JsonResponseHandler();
            $jsonHandler->addTraceToOutput(true);
            $run->pushHandler($jsonHandler);
        }

        $run->register();

        $response = $response->withStatus(Utils::getStatusCode($e, $response));
        $response->getBody()->write($run->handleException($e));

        return $response;
    }
}
