<?php
namespace My\Web\Lib\View;

interface ViewAwareInterface
{
    /**
     * @param View $view
     */
    public function setView(View $view);
}
