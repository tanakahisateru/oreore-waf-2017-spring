<?php
namespace My\Web\Lib\View\Asset;

use Webmozart\PathUtil\Path;

class ResourceBundle implements UrlCollectableInterface
{
    /**
     * @var AssetManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var array
     */
    protected $files;

    /**
     * @var string
     */
    protected $stage;

    /**
     * @var UrlCollectableInterface[]|string[]
     */
    protected $dependencies = [];

    /**
     * Asset constructor.
     *
     * @param AssetManager $manager
     * @param string $baseUrl
     * @param array $files
     * @param string|null $stage
     * @param UrlCollectableInterface[]|string[] $dependencies
     */
    public function __construct(AssetManager $manager, $baseUrl, array $files, $stage, array $dependencies = [])
    {
        $this->manager = $manager;
        $this->baseUrl = $baseUrl;
        $this->files = $files;
        $this->stage = $stage;
        $this->dependencies = $dependencies;
    }

    /**
     * @param string $stage
     * @return array
     */
    public function collectUrls($stage = null)
    {
        $urls = $this->collectDependencyUrls($stage);
        if ($this->matchesStageTo($stage)) {
            $urls = array_merge($urls, $this->ownUrls());
        }
        return array_unique($urls);
    }

    /**
     * @param string $stage
     * @return array
     */
    protected function collectDependencyUrls($stage)
    {
        $urls = [];
        foreach ($this->dependencies as $dependency) {
            $dependency = $this->ensureObject($dependency);
            $urls = array_merge($urls, $dependency->collectUrls($stage));
        }
        return array_unique($urls);
    }

    /**
     * @param mixed $dependency
     * @return UrlCollectableInterface
     */
    private function ensureObject($dependency)
    {
        if (!is_scalar($dependency)) {
            return $dependency;
        }

        if (!$this->manager->has($dependency)) {
            throw new \RuntimeException('Missing asset dependency found: ' . $dependency);
        }

        return $this->manager->get($dependency);
    }

    /**
     * @param string|null $stage
     * @return bool
     */
    private function matchesStageTo($stage)
    {
        return empty($this->stage) || empty($stage) || $stage == $this->stage;
    }

    /**
     * @return array
     */
    private function ownUrls()
    {
        return array_map(function ($file) {
            $url = Path::join($this->baseUrl, $file);
            return $this->manager->toManagedUrl($url);
        }, $this->files);
    }
}
