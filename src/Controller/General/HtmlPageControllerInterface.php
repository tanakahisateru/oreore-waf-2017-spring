<?php
namespace My\Web\Controller\General;

use My\Web\Lib\Http\HttpFactoryAwareInterface;
use My\Web\Lib\View\ViewAwareInterface;

interface HtmlPageControllerInterface extends HttpFactoryAwareInterface, ViewAwareInterface
{

}
