<?php
namespace My\Web\Lib\View;

class Template extends \League\Plates\Template\Template
{
    /**
     * @return View
     */
    public function view()
    {
        $engine = $this->engine;
        assert($engine instanceof TemplateEngine);
        return $engine->getView();
    }

    /**
     * @inheritdoc
     */
    public function escape($string, $functions = null)
    {
        return $this->escapeHtml($string, $functions);
    }

    /**
     * @param string $string
     * @param array|null $functions
     * @return string
     */
    public function escapeHtml($string, $functions = null)
    {
        return $this->delegateToEscaper('escapeHtml', $string, $functions);
    }

    /**
     * @param string $string
     * @param array|null $functions
     * @return string
     */
    public function escapeHtmlAttr($string, $functions = null)
    {
        return $this->delegateToEscaper('escapeHtmlAttr', $string, $functions);
    }

    /**
     * @param string $string
     * @param array|null $functions
     * @return string
     */
    public function escapeJs($string, $functions = null)
    {
        return $this->delegateToEscaper('escapeJs', $string, $functions);
    }

    /**
     * @param string $string
     * @param array|null $functions
     * @return string
     */
    public function escapeCss($string, $functions = null)
    {
        return $this->delegateToEscaper('escapeCss', $string, $functions);
    }

    /**
     * @param string $string
     * @param array|null $functions
     * @return string
     */
    public function escapeUrl($string, $functions = null)
    {
        return $this->delegateToEscaper('escapeUrl', $string, $functions);
    }

    /**
     * @param string $method
     * @param string $string
     * @param array|null $functions
     * @return string
     */
    protected function delegateToEscaper($method, $string, $functions = null)
    {
        if ($functions) {
            $string = $this->batch($string, $functions);
        }
        $engine = $this->engine;
        assert($engine instanceof TemplateEngine);
        return $engine->getEscaper()->$method($string);
    }
}
