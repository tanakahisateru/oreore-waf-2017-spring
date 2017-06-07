<?php
namespace Acme\App\Router;

use Http\Factory\Diactoros\ResponseFactory;
use Http\Factory\Diactoros\ServerRequestFactory;
use Http\Factory\Diactoros\StreamFactory;
use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ActionDispatcherTest extends TestCase
{
    /**
     * @var ControllerProvider
     */
    protected $controllerProvider;

    /**
     * @var ServerRequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    public function testDispatchToPsr7SinglePassCallable()
    {
        $dispatcher = new ActionDispatcher($this->controllerProvider, $this->streamFactory);

        $self = $this;
        $handler = function (ServerRequestInterface $request) use ($self) {
            assert($request->getMethod() == 'GET');
            // NEVER USE `$this` here because Aura.Dispatcher has a bug treating closure as function.
            $response = $self->responseFactory->createResponse(200);
            $response = $response->withHeader('Content-Type', 'text/html');
            $response->getBody()->write('callable handler response');
            return $response;
        };

        $response = $dispatcher->dispatch([
            'controller' => $handler,
            'request' => $this->requestFactory->createServerRequest('GET', '/'),
            'response' => $this->responseFactory->createResponse(),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
        $stream = $response->getBody();
        $stream->rewind();
        $this->assertContains('callable handler response', $stream->getContents());
    }

    public function testDispatchToStringReturningCallable()
    {
        $dispatcher = new ActionDispatcher($this->controllerProvider, $this->streamFactory);

        $handler = function () {
            return 'callable handler response';
        };

        $response = $dispatcher->dispatch([
            'controller' => $handler,
            'request' => $this->requestFactory->createServerRequest('GET', '/'),
            'response' => $this->responseFactory->createResponse(),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty($response->getHeaderLine('Content-Type')); // No content-type header!
        $stream = $response->getBody();
        $stream->rewind();
        $this->assertContains('callable handler response', $stream->getContents());
    }

    public function testDispatchToArrayReturningCallable()
    {
        $dispatcher = new ActionDispatcher($this->controllerProvider, $this->streamFactory);

        $handler = function () {
            return ['foo' => 'bar'];
        };

        $response = $dispatcher->dispatch([
            'controller' => $handler,
            'request' => $this->requestFactory->createServerRequest('GET', '/'),
            'response' => $this->responseFactory->createResponse(),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $stream = $response->getBody();
        $stream->rewind();
        $this->assertJsonStringEqualsJsonString(json_encode(['foo' => 'bar']), $stream->getContents());
    }

    public function testDispatchToStreamOutputCallable()
    {
        $dispatcher = new ActionDispatcher($this->controllerProvider, $this->streamFactory);

        $handler = function () {
            echo "callable handler response";
        };

        $response = $dispatcher->dispatch([
            'controller' => $handler,
            'request' => $this->requestFactory->createServerRequest('GET', '/'),
            'response' => $this->responseFactory->createResponse(),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty($response->getHeaderLine('Content-Type')); // No content-type header!
        $stream = $response->getBody();
        $stream->rewind();
        $this->assertContains('callable handler response', $stream->getContents());
    }

    protected function setUp()
    {
        $this->controllerProvider = $this->createMock(ControllerProvider::class);
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
        $this->streamFactory = new StreamFactory();
    }
}
