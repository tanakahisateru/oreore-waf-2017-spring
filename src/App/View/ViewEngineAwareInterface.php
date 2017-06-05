<?php
namespace Acme\App\View;

interface ViewEngineAwareInterface
{
    /**
     * @param ViewEngine $viewEngine
     */
    public function setViewEngine(ViewEngine $viewEngine);
}
