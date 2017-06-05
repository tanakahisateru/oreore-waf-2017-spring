<?php
namespace Acme\App\View\Template;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Zend\Escaper\Escaper;

class EscaperExtension implements ExtensionInterface
{
    /**
     * @var Escaper
     */
    protected $escaper;

    // public $template;

    /**
     * EscaperExtension constructor.
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * @inheritDoc
     */
    public function register(Engine $engine)
    {
        $engine->registerFunction('escapeHtml', [$this, 'escapeHtml']);
        $engine->registerFunction('escapeHtmlAttr', [$this, 'escapeHtmlAttr']);
        $engine->registerFunction('escapeJs', [$this, 'escapeJs']);
        $engine->registerFunction('escapeCss', [$this, 'escapeCss']);
        $engine->registerFunction('escapeUrl', [$this, 'escapeUrl']);
    }

    /**
     * @param string $string
     * @return string
     */
    public function escapeHtml($string)
    {
        return $this->escaper->escapeHtml($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public function escapeHtmlAttr($string)
    {
        return $this->escaper->escapeHtmlAttr($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public function escapeJs($string)
    {
        return $this->escaper->escapeJs($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public function escapeCss($string)
    {
        return $this->escaper->escapeCss($string);
    }

    /**
     * @param string $string
     * @return string
     */
    public function escapeUrl($string)
    {
        return $this->escaper->escapeUrl($string);
    }
}
