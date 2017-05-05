<?php
namespace My\Web\Lib\Container\Injection;

use Aura\Di\Injection\LazyInterface;
use Psr\Container\ContainerInterface;

class InjectionFactory extends \Aura\Di\Injection\InjectionFactory
{
    /**
     * @param string $class
     * @param array $params
     * @param array $setters
     * @param callable $builder
     * @return mixed
     */
    public function newInstance(
        $class,
        array $params = [],
        array $setters = [],
        $builder = null
    )
    {
        $object = parent::newInstance($class, $params, $setters);
        if ($builder) {
            $builder($object);
        }
        return $object;
    }

    /**
     * @param string $class
     * @param array $params
     * @param array $setters
     * @param callable $builder
     * @return Factory
     */
    public function newFactory(
        $class,
        array $params = [],
        array $setters = [],
        $builder = null
    )
    {
        return new Factory($this->resolver, $class, $params, $setters, $builder);
    }

    /**
     * @param string $class
     * @param array $params
     * @param array $setters
     * @param callable $builder
     * @return LazyNew
     */
    public function newLazyNew(
        $class,
        array $params = [],
        array $setters = [],
        $builder = null
    ) {
        return new LazyNew($this->resolver, $class, $params, $setters, $builder);
    }

    /**
     * @param LazyInterface|string $file
     * @param LazyInterface|array $params
     * @return LazyRequire
     */
    public function newLazyRequire($file, $params = [])
    {
        return new LazyRequire($file, $params);
    }

    /**
     * @param LazyInterface|string $file
     * @param LazyInterface|array $params
     * @return LazyInclude
     */
    public function newLazyInclude($file, $params = [])
    {
        return new LazyInclude($file, $params);
    }

    /**
     * @param LazyInterface|string $file
     * @param string $objectName
     * @param LazyInterface|array $params
     * @return callable
     */
    public function newRequireBuilder($file, $objectName, $params = [])
    {
        return function ($object) use ($file, $objectName, $params) {
            if ($params instanceof LazyInterface) {
                $params = $params->__invoke();
            }
            $params[$objectName] = $object;
            $this->newLazyRequire($file, $params)->__invoke();
        };
    }

    /**
     * @param LazyInterface|string $file
     * @param string $objectName
     * @param LazyInterface|array $params
     * @return callable
     */
    public function newIncludeBuilder($file, $objectName, $params = [])
    {
        return function ($object) use ($file, $objectName, $params) {
            if ($params instanceof LazyInterface) {
                $params = $params->__invoke();
            }
            $params[$objectName] = $object;
            $this->newLazyInclude($file, $params)->__invoke();
        };
    }

    /**
     * @param ContainerInterface $container
     * @param string $serviceName
     * @return callable
     */
    public function newCallbackReturns(ContainerInterface $container, $serviceName)
    {
        return function () use ($container, $serviceName) {
            return $container->get($serviceName);
        };
    }
}
