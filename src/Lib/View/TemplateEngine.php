<?php
namespace My\Web\Lib\View;

use League\Plates\Engine;
use Zend\Escaper\Escaper;

class TemplateEngine extends Engine
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * TemplateEngine constructor.
     * @param string $directory
     * @param string $fileExtension
     * @param string $encoding
     */
    public function __construct($directory = null, $fileExtension = null, $encoding = 'utf-8')
    {
        parent::__construct($directory, $fileExtension);

        $this->escaper = new Escaper($encoding);
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
