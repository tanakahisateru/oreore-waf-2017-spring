<?php
namespace My\Web\Lib\View\Asset;

use Webmozart\PathUtil\Path;

class Asset implements AssetInterface
{
    /**
     * @var AssetManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var array
     */
    protected $elements;

    /**
     * @var string
     */
    protected $stage;

    /**
     * @var AssetInterface[]|string[]
     */
    protected $dependencies = [];

    /**
     * Asset constructor.
     *
     * @param AssetManager $manager
     * @param string $baseUrl
     * @param array $elements
     * @param string $stage
     * @param AssetInterface[] $dependencies
     */
    public function __construct(AssetManager $manager, $baseUrl, array $elements, $stage, array $dependencies = [])
    {
        $this->manager = $manager;
        $this->baseUrl = $baseUrl;
        $this->elements = $elements;
        $this->stage = $stage;
        $this->dependencies = $dependencies;
    }

    /**
     * @param string $stage
     * @return array
     */
    public function elements($stage = null)
    {
        if (empty($this->stage) || empty($stage) || $stage == $this->stage) {
            return $this->elements;
        }

        return [];
    }

    /**
     * @return AssetInterface[]
     */
    public function collectDependencies()
    {
        $summary = [];

        foreach ($this->dependencies as $dependency) {
            if (is_scalar($dependency)) {
                if (!$this->manager->has($dependency)) {
                    throw new \RuntimeException('Missing asset dependency found: ' . $dependency);
                }
                $dependency = $this->manager->get($dependency);
            }

            foreach ($dependency->collectDependencies() as $nestedDependency) {
                if (!in_array($nestedDependency, $summary, true)) {
                    $summary[] = $nestedDependency;
                }
            }

            if (!in_array($dependency, $summary, true)) {
                $summary[] = $dependency;
            }
        }

        return $summary;
    }
    
    /**
     * @param string $stage
     * @return array
     */
    public function collectUrls($stage = null)
    {
        $urls = [];

        foreach ($this->collectDependencies() as $dependency) {
            $urls = array_merge($urls, $dependency->collectUrls($stage));
        }

        foreach ($this->elements($stage) as $element) {
            $urls[] = Path::join($this->baseUrl, $element);
        }

        return array_unique(array_map([$this->manager, 'toUrl'], $urls));
    }
}
