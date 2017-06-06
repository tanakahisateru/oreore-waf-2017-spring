<?php
namespace Acme\App\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sumeko\Http\Exception\NotFoundException;

class NotFoundHandler implements MiddlewareInterface
{
    /**
     * @inheritDoc
     * @throws NotFoundException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        throw new NotFoundException();
    }
}
