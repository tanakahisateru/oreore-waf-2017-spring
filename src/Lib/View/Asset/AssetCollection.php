<?php
namespace My\Web\Lib\View\Asset;

class AssetCollection
{
    /**
     * @var AssetManager
     */
    protected $manager;

    /**
     * @var AssetInterface[]
     */
    protected $assets;

    /**
     * AssetCollection constructor.
     *
     * @param AssetManager $manager
     */
    public function __construct(AssetManager $manager)
    {
        $this->manager = $manager;
        $this->assets = [];
    }

    /**
     * @param AssetInterface|string $asset
     */
    public function add($asset)
    {
        if (!($asset instanceof AssetInterface)) {
            $a = $this->manager->getAsset($asset);
            if (!$a) {
                throw new \UnexpectedValueException('No such asset: ' . $asset);
            }
            $asset = $a;
        }

        $this->assets[] = $asset;
    }

    /**
     * @param string $stage
     * @return array
     */
    public function collectUrls($stage = null)
    {
        $urls = [];
        foreach ($this->assets as $asset) {
            foreach ($asset->collectUrls($stage) as $url) {
                if (!in_array($url, $urls)) {
                    $urls[] = $url;
                }
            }
        }
        // TODO Replace URLs to unified and rev-ed version.
        return $urls;
    }
}
