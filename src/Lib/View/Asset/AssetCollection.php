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
            if (!$this->manager->has($asset)) {
                throw new \UnexpectedValueException('No such asset: ' . $asset);
            }
            $asset = $this->manager->get($asset);
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
                $urls[] = $url;
            }
        }

        return array_unique($urls);
    }
}
