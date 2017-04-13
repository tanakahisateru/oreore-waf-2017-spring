<?php
namespace My\Web\Lib\View\Asset;

trait AssetTrait
{
    protected $name;

    protected $baseUrl;

    /**
     * @var AssetInterface[]
     */
    protected $dependencies = [];

    public function getName()
    {
        return $this->name;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return AssetInterface[]
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @return AssetInterface[]
     */
    public function collectDependencies()
    {
        $dependencies = [];

        foreach ($this->getDependencies() as $dependency) {
            foreach ($dependency->collectDependencies() as $nestedDependency) {
                if (!isset($dependencies[$nestedDependency->getName()])) {
                    $dependencies[$nestedDependency->getName()] = $nestedDependency;
                }
            }
            if (!isset($dependencies[$dependency->getName()])) {
                $dependencies[$dependency->getName()] = $dependency;
            }
        }

        return array_values($dependencies);
    }
}
