<?php
namespace My\Web\Lib\View;

interface ViewAwareInterface
{
    /**
     * @param ViewEngine $viewEngine
     */
    public function setViewEngine(ViewEngine $viewEngine);
}
