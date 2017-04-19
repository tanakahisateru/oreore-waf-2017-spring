<?php
namespace My\Web\Lib\View\Asset;

class AssetManager
{
    /**
     * @var AssetInterface[]
     */
    protected $assets = [];

    /**
     * @var array
     */
    protected $unifiedMap = [];

    /**
     * @var array
     */
    protected $revManifest = [];


    /**
     * @param string $name
     * @param AssetInterface $asset
     */
    public function register($name, AssetInterface $asset)
    {
        $this->assets[$name] = $asset;
    }

    public function newAsset(array $definition = [])
    {
        $baseUrl = isset($definition['baseUrl']) ? $definition['baseUrl'] : '';

        $elements = isset($definition['elements']) ? $definition['elements'] : [];
        if (!is_array($elements)) {
            $elements = [$elements];
        }

        $stage = isset($definition['stage']) ? $definition['stage'] : null;

        $dependencies = [];
        if (isset($definition['dependencies'])) {
            if (!is_array($definition['dependencies'])) {
                $definition['dependencies'] = [$definition['dependencies']];
            }

            foreach ($definition['dependencies'] as $dependency) {
                if ($dependency instanceof AssetInterface || is_scalar($dependency)) {
                    $dependencies[] = $dependency;
                } elseif (is_array($dependency)) {
                    if (empty($dependency['baseUrl']) || !is_string($dependency['baseUrl'])) {
                        $dependency['baseUrl'] = $baseUrl;
                    }
                    if (empty($dependency['stage']) || !is_string($dependency['stage'])) {
                        $dependency['stage'] = $stage;
                    }
                    $dependencies[] = $this->newAsset($dependency);
                } else {
                    throw new \InvalidArgumentException('Asset dependency must be string, array or Asset object');
                }
            }
        }

        return new Asset($this, $baseUrl, $elements, $stage, $dependencies);
    }

    /**
     * @param $name
     * @return AssetInterface
     */
    public function getAsset($name)
    {
        return isset($this->assets[$name]) ? $this->assets[$name] : null;
    }

    public function mapUnified($url, $glob)
    {
        
    }

    public function revManifest($manifest)
    {

    }
}
