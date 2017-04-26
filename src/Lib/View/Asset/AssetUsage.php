<?php
namespace My\Web\Lib\View\Asset;

class AssetUsage implements UrlCollectableInterface
{
    /**
     * @var AssetManager
     */
    protected $manager;

    /**
     * @var UrlCollectableInterface[]
     */
    protected $assets;

    /**
     * AssetUsage constructor.
     *
     * @param AssetManager $manager
     */
    public function __construct(AssetManager $manager)
    {
        $this->manager = $manager;
        $this->assets = [];
    }

    /**
     * @param UrlCollectableInterface|string $asset
     */
    public function add($asset)
    {
        if (!($asset instanceof UrlCollectableInterface)) {
            if (!$this->manager->has($asset)) {
                throw new \UnexpectedValueException('No such asset: ' . $asset);
            }
            $asset = $this->manager->get($asset);
        }

        $this->assets[] = $asset;
    }

    /**
     * @param string $section
     * @return array
     */
    public function collectUrls($section = null)
    {
        $urls = [];
        foreach ($this->assets as $asset) {
            $urls = array_merge($urls, $asset->collectUrls($section));
        }
        return array_unique($urls);
    }
}
