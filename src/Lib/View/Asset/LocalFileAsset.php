<?php
namespace My\Web\Lib\View\Asset;

use Webmozart\PathUtil\Path;

class LocalFileAsset implements AssetInterface
{
    use AssetTrait;

    /**
     * @var array
     */
    protected $elements;

    /**
     * @var string
     */
    protected $stage;

    /**
     * LocalFileAsset constructor.
     * @param string $name
     * @param string $baseUrl
     * @param array $elements
     * @param string $stage
     * @param AssetInterface[] $dependencies
     */
    public function __construct($name, $baseUrl, array $elements, $stage, array $dependencies = [])
    {
        $this->name = $name;
        $this->baseUrl = $baseUrl;
        $this->elements = $elements;
        $this->stage = $stage;
        $this->dependencies = $dependencies;
    }

    /**
     * @param string $stage
     * @return array
     */
    public function getElements($stage = null)
    {
        if ($this->stage && $stage == $this->stage) {
            return $this->elements;
        }

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
            $urls = array_merge($urls, $dependency->collectUrls($stage));
        }

        foreach ($this->getElements($stage) as $element) {
            $urls[] = Path::join($this->getBaseUrl(), $element);
        }

        return $urls;
    }
}
