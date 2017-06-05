<?php
namespace Acme\App\View\Template;

use Acme\App\View\View;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

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
