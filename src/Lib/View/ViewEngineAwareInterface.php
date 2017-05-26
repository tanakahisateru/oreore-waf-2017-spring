<?php
namespace My\Web\Lib\View;

interface ViewEngineAwareInterface
{
    /**
     * @param ViewEngine $viewEngine
     */
    public function setViewEngine(ViewEngine $viewEngine);
}
