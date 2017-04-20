<?php
namespace My\Web\Lib\Container;

use Psr\Container\ContainerInterface;

class AliasContainer implements ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $parent;

    /**
     * @var array
     */
    protected $alias;

    /**
     * AliasContainer constructor.
     * @param ContainerInterface $parent
     * @param array $alias
     */
    public function __construct(ContainerInterface $parent, array $alias)
    {
        $this->parent = $parent;
        $this->alias = $alias;
    }


    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if (!isset($this->alias[$id])) {
            throw new NotFoundException('Not found id: ' . $id);
        }
        return $this->parent->get($this->alias[$id]);
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return isset($this->alias[$id]) && $this->parent->has($this->alias[$id]);
    }
}
