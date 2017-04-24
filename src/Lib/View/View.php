<?php
namespace My\Web\Lib\View;

use My\Web\Lib\View\Asset\AssetUsage;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

class View implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    // Category tag for system-wide event listener
    public $eventIdentifier = ['view'];

    /**
     * @var ViewEngine
     */
    protected $engine;

    /**
     * @var array
     */
    protected $folderMap;

    /**
     * @var array
     */
    protected $attributeCollection;

    /**
     * @var AssetUsage
     */
    protected $requiredAssets;

    /**
     * View constructor.
     *
     * @param ViewEngine $engine
     * @param AssetUsage $requiredAssets
     */
    public function __construct($engine, $requiredAssets)
    {
        $this->engine = $engine;

        $this->folderMap = [];
        $this->attributeCollection = [];
        $this->requiredAssets = $requiredAssets;
    }

    /**
     * @return array
     */
    public function getFolderMap()
    {
        return $this->folderMap;
    }

    /**
     * @param string $folderName
     * @return bool
     */
    public function hasFolder($folderName)
    {
        return isset($this->folderMap[$folderName]);
    }

    /**
     * @param string $folderName
     * @return string
     */
    public function getFolder($folderName)
    {
        if ($this->hasFolder($folderName)) {
            return $this->folderMap[$folderName];
        } else {
            return null;
        }
    }

    /**
     * @param string $folderName
     * @param string $subPath
     */
    public function setFolder($folderName, $subPath)
    {
        $this->folderMap[$folderName] = $subPath;
    }

    /**
     * @return array
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollection;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->attributeCollection[$name]);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->hasAttribute($name) ? $this->attributeCollection[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        $this->attributeCollection[$name] = $value;
    }
    /**
     * @param string $name
     * @param array $data
     * @param bool $raw
     * @return bool
     */
    public function routeUrlTo($name, $data=[], $raw = false)
    {
        return $this->engine->routeUrlTo($name, $data, $raw);
    }

    /**
     * @param string $url
     * @return string
     */
    public function resourceUrlTo($url)
    {
        return $this->engine->resourceUrlTo($url);
    }

    /**
     * @param string $name
     */
    public function requireAsset($name)
    {
        $this->requiredAssets->add($name);
    }

    /**
     * @param string $stage
     * @return array
     */
    public function assetUrls($stage = null)
    {
        return $this->requiredAssets->collectUrls($stage);
    }

    /**
     * @param string $name
     * @param array $data
     * @return string
     */
    public function render($name, array $data = [])
    {
        return $this->engine->renderIn($this, $name, $data);
    }
}
