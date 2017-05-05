<?php
namespace My\Web\Lib\Container;

use My\Web\Lib\Container\Injection\InjectionFactory;

class ContainerBuilder extends \Aura\Di\ContainerBuilder
{
    /**
     * @param bool $autoResolve
     * @return Container
     */
    public function newInstance($autoResolve = false)
    {
        $resolver = $this->newResolver($autoResolve);
        return new Container(new InjectionFactory($resolver));
    }
}
