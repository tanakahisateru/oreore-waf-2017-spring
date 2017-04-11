<?php
namespace My\Web\Lib\View;

use League\Plates\Engine;
use Zend\Escaper\Escaper;

class TemplateEngine extends Engine
{
    protected $view;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * TemplateEngine constructor.
     * @param View $view
     * @param callable $builder
     * @param string $encoding
     */
    public function __construct(View $view, $builder = null, $encoding = 'utf-8')
    {
        parent::__construct(null, null);

        $this->view = $view;
        $this->escaper = new Escaper($encoding);

        if ($builder) {
            $builder($this);
        }
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * @param string $name
     * @return Template
     */
    public function make($name)
    {
        return new Template($this, $name);
    }
}
