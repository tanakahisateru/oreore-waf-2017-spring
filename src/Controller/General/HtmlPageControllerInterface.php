<?php
namespace Acme\Controller\General;

use Acme\App\Http\StreamFactoryAwareInterface;
use Acme\App\Router\RouterAwareInterface;
use Acme\App\View\ViewFactoryAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Zend\EventManager\EventManagerAwareInterface;

interface HtmlPageControllerInterface extends
    LoggerAwareInterface,
    EventManagerAwareInterface,
    StreamFactoryAwareInterface,
    RouterAwareInterface,
    ViewFactoryAwareInterface
{
    /**
     * @param string $path
     */
    public function templateFolder($path);

    /**
     * @param ResponseInterface $responsePrototype
     */
    public function setResponsePrototype(ResponseInterface $responsePrototype);
}
