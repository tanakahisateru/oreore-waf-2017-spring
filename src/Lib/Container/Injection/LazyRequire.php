<?php
namespace My\Web\Lib\Container\Injection;

use Aura\Di\Injection\LazyInterface;

class LazyRequire extends \Aura\Di\Injection\LazyRequire
{
    /**
     * @var LazyInterface|array
     */
    protected $params;

    /**
     * LazyRequire constructor.
     * @param LazyInterface|string $file
     * @param LazyInterface|array $params
     */
    public function __construct($file, $params = [])
    {
        parent::__construct($file);
        $this->params = $params;
    }

    /**
     * @inheritDoc
     */
    public function __invoke()
    {
        $filename = $this->file;
        if ($filename instanceof LazyInterface) {
            $filename = $filename->__invoke();
        }

        $params = $this->params;
        if ($params instanceof LazyInterface) {
            $params = $params->__invoke();
        }

        if (is_array($params)) {
            extract($params);
        }

        /** @noinspection PhpIncludeInspection */
        return require $filename;
    }
}
