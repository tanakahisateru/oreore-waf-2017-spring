<?php
namespace My\Web\Lib\Util;

use DebugBar\DebugBar;
use League\Plates\Template\Template;

class DebugBarInsertion
{
    /**
     * @param DebugBar $debugbar
     * @param Template $template
     * @param string $headerSlot
     * @param string $bodySlot
     * @param bool $push
     */
    public static function exec($debugbar, $template, $headerSlot, $bodySlot, $push = true)
    {
        $method = $push ? 'push' : 'start';

        $renderer = $debugbar->getJavascriptRenderer('/assets/debugbar');

        $template->$method($headerSlot);
        echo $renderer->renderHead();
        $template->end();

        $template->$method($bodySlot);
        echo $renderer->render();
        $template->end();

    }
}
