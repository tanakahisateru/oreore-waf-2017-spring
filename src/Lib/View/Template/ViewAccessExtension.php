<?php
namespace My\Web\Lib\View\Template;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use My\Web\Lib\View\View;

class ViewAccessExtension implements ExtensionInterface
{
    /**
     * @var View
     */
    public $view;

    /**
     * ViewAccessExtension constructor.
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * @inheritDoc
     */
    public function register(Engine $engine)
    {
        $engine->registerFunction('view', [$this, 'view']);
    }

    /**
     * @return View
     */
    public function view()
    {
        return $this->view;
    }
}
