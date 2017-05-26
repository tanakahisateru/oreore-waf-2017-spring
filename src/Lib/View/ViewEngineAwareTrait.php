<?php
namespace My\Web\Lib\View;

trait ViewEngineAwareTrait
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

}
