<?php
namespace Acme\App\View;

trait ViewFactoryAwareTrait
{
    /**
     * @var callable
     */
    protected $viewFactory;

    /**
     * @param callable $viewFactory
     */
    public function setViewFactory(callable $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    protected function createView()
    {
        return call_user_func($this->viewFactory);
    }
}
