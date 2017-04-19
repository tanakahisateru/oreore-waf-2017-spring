<?php
namespace My\Web\Lib\View;

trait ViewInjectionTrait
{
    /**
     * @var ViewEngine
     */
    protected $viewEngine;

    /**
     * @param ViewEngine $viewEngine
     */
    public function setViewEngine(ViewEngine $viewEngine)
    {
        $this->viewEngine = $viewEngine;
    }

    /**
     * @return View
     */
    public function createView()
    {
        return $this->viewEngine->createView();
    }
}
