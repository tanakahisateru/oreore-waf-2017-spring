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
     * @param AssetInterface|mixed $asset
     */
    public function registerAsset(AssetInterface $asset)
    {
        $this->assets[$asset->getName()] = $asset;
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

    public function mapRevision($manifest)
    {

    }

    /**
     * @param AssetInterface[] $assets
     * @param string $stage
     * @return array
     */
    public function collectAllUrls($assets, $stage = null)
    {
        $urls = [];

        foreach ($assets as $asset) {
            foreach ($asset->collectUrls($stage) as $url) {
                if (!in_array($url, $urls)) {
                    $urls[] = $url;
                }
            }
        }

        return $urls;
    }
}
