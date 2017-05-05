<?php
namespace My\Web\Lib\Container\Injection;

use Aura\Di\Resolver\Resolver;

class Factory extends \Aura\Di\Injection\Factory
{
    protected $builder;

    /**
     * @param Resolver $resolver
     * @param string $class
     * @param array $params
     * @param array $setters
     * @param callable $builder
     */
    public function __construct(Resolver $resolver, $class, array $params = [], array $setters = [], $builder)
    {
        parent::__construct($resolver, $class, $params, $setters);
        $this->builder = $builder;
    }

    /**
     * @return object
     */
    public function __invoke()
    {
        $object = parent::__invoke();
        $builder = $this->builder;
        if ($builder) {
            $builder($object);
        }
        return $object;
    }
}
