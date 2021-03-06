<?php
namespace Acme\App\Debug\Middleware\Generator;

use Acme\App\Middleware\Generator\ErrorResponseGenerator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sumeko\Http\ClientException;
use Sumeko\Http\Exception as HttpException;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Util\Misc;

class WhoopsErrorResponseGenerator
{
    /**
     * @var ErrorResponseGenerator
     */
    protected $delegateGenerator;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * WhoopsErrorResponseGenerator constructor.
     * @param ErrorResponseGenerator $delegateGenerator
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(
        ErrorResponseGenerator $delegateGenerator,
        ResponseFactoryInterface $responseFactory
    )
    {
        $this->delegateGenerator = $delegateGenerator;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Create/update the response representing the error.
     *
     * @param \Throwable $e
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function __invoke(\Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        if ($e instanceof ClientException) {
            // Delegates default error handler if it raised from user operation.
            return $this->delegateGenerator->__invoke($e, $request);
        }

        $response = $this->responseFactory->createResponse();
        if ($e instanceof HttpException) {
            $response = $response->withStatus($e->getCode(), $e->getMessage());
        } else {
            $response = $response->withStatus(500);
        }

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

        $response->getBody()->write($run->handleException($e));

        return $response;
    }
}
