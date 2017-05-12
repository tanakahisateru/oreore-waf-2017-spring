<?php
namespace My\Web\Lib\View\Asset;

use Webmozart\PathUtil\Path;

class Asset implements UrlCollectableInterface
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
    protected $section;

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
     * @param string|null $section
     * @param UrlCollectableInterface[]|string[] $dependencies
     */
    public function __construct(AssetManager $manager, $baseUrl, array $files, $section, array $dependencies = [])
    {
        $this->manager = $manager;
        $this->baseUrl = $baseUrl;
        $this->files = $files;
        $this->section = $section;
        $this->dependencies = $dependencies;
    }

    /**
     * @param string $section
     * @return array
     */
    public function collectUrls($section = null)
    {
        $urls = $this->collectDependencyUrls($section);
        if ($this->matchesSectionTo($section)) {
            $urls = array_merge($urls, $this->ownUrls());
        }
        return array_unique($urls);
    }

    /**
     * @param string $section
     * @return array
     */
    protected function collectDependencyUrls($section)
    {
        $urls = [];
        foreach ($this->dependencies as $dependency) {
            $dependency = $this->ensureObject($dependency);
            $urls = array_merge($urls, $dependency->collectUrls($section));
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
     * @param string|null $section
     * @return bool
     */
    private function matchesSectionTo($section)
    {
        return empty($this->section) || empty($section) || $section == $this->section;
    }

    /**
     * @return array
     */
    private function ownUrls()
    {
        return array_map(function ($file) {
            $url = Path::join($this->baseUrl, $file);
            return $this->manager->url($url);
        }, $this->files);
    }
}
