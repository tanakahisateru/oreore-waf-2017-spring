<?php
namespace My\Web\Lib\App;

use Aura\Di\Container;
use Interop\Http\ServerMiddleware\MiddlewareInterface;

class WebApp extends App
{
    /**
     * @var MiddlewareInterface|callable
     */
    protected $middlewarePipe;

    /**
     * WebApp constructor.
     * @param Container $container
     * @param MiddlewareInterface|callable $middlewarePipe
     */
    public function __construct(Container $container, $middlewarePipe)
    {
        parent::__construct($container);
        $this->middlewarePipe = $middlewarePipe;
    }

    /**
     * @return MiddlewareInterface|callable
     */
    public function getMiddlewarePipe()
    {
        return $this->middlewarePipe;
    }

    /**
     *
     */
    public function run()
    {
    }
}
