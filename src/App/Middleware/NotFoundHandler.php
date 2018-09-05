<?php
namespace Acme\App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sumeko\Http\Exception\NotFoundException;

class NotFoundHandler implements MiddlewareInterface
{
    /**
     * @inheritDoc
     * @throws NotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        throw new NotFoundException();
    }
}
