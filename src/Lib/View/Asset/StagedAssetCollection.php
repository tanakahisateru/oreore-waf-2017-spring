<?php
namespace My\Web\Lib\View\Asset;

class StagedAssetCollection implements AssetInterface
{
    use AssetTrait;

    /**
     * @var AssetInterface[][]
     */
    protected $components = [];

    /**
     * StagedAssetCollection constructor.
     * @param string $name
     * @param array $components
     * @param AssetInterface[] $dependencies
     */
    public function __construct($name, array $components, array $dependencies = [])
    {
        $this->components = $components;
    }

    /**
     * @param string $stage
     * @return array
     */
    public function getElements($stage = null)
    {
        return [];
    }

    /**
     * @param string $stage
     * @return array
     */
    public function collectUrls($stage = null)
    {
        $urls = [];

        foreach ($this->collectDependencies() as $dependency) {
            foreach ($dependency->collectUrls($stage) as $url) {
                $urls[] = $url;
            }
        }

        foreach ($this->components as $component) {
            foreach ($component->collectUrls($stage) as $url) {
                $urls[] = $url;
            }
        }

        return $urls;
    }
}
