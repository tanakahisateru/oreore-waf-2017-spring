<?php
namespace Acme\App\View;

interface ViewFactoryAwareInterface
{
    /**
     * @param callable $viewFactory
     */
    public function setViewFactory(callable $viewFactory);
}
