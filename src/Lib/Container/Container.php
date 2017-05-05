<?php
namespace My\Web\Lib\Container;

use Aura\Di\Injection\LazyInterface;
use My\Web\Lib\Container\Injection\InjectionFactory;
use My\Web\Lib\Container\Injection\LazyInclude;
use My\Web\Lib\Container\Injection\LazyRequire;

class Container extends \Aura\Di\Container
{
    /**
     * @var InjectionFactory
     */
    protected $injectionFactory;

    public function newInstance(
        $class,
        array $mergeParams = [],
        array $mergeSetters = [],
        $builder = null
    )
    {
        $this->locked = true;
        return $this->injectionFactory->newInstance($class, $mergeParams, $mergeSetters, $builder);
    }

    public function newFactory(
        $class,
        array $params = [],
        array $setters = [],
        $builder = null
    )
    {
        return $this->injectionFactory->newFactory($class, $params, $setters, $builder);
    }

    public function lazyNew(
        $class,
        array $params = [],
        array $setters = [],
        $builder = null
    )
    {
        return $this->injectionFactory->newLazyNew($class, $params, $setters, $builder);
    }

    /**
     * @param string $file The file to require.
     * @param array $params
     * @return LazyRequire
     */
    public function lazyRequire($file, $params = [])
    {
        return $this->injectionFactory->newLazyRequire($file, $params);
    }

    /**
     * @param string $file The file to require.
     * @param array $params
     * @return LazyInclude
     */
    public function lazyInclude($file, $params = [])
    {
        return $this->injectionFactory->newLazyInclude($file, $params);
    }

    /**
     * @param LazyInterface|string $file
     * @param string $objectName
     * @param LazyInterface|array $params
     * @return callable
     */
    public function requireBuilder($file, $objectName, $params = [])
    {
        return $this->injectionFactory->newRequireBuilder($file, $objectName, $params);
    }

    /**
     * @param LazyInterface|string $file
     * @param string $objectName
     * @param LazyInterface|array $params
     * @return callable
     */
    public function includeBuilder($file, $objectName, $params = [])
    {
        return $this->injectionFactory->newIncludeBuilder($file, $objectName, $params);
    }

    /**
     * @param string $serviceName
     * @return callable
     */
    public function callbackReturns($serviceName)
    {
        return $this->injectionFactory->newCallbackReturns($this, $serviceName);
    }
}
