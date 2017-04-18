<?php
namespace My\Web\Lib\View;

use My\Web\Lib\View\Asset\AssetInterface;
use Psr\Http\Message\ResponseInterface;

class View
{
    /**
     * @var ViewEngine
     */
    protected $engine;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var AssetInterface[]
     */
    protected $requiredAssets;

    /**
     * View constructor.
     *
     * @param ViewEngine $engine
     */
    public function __construct($engine)
    {
        $this->engine = $engine;

        $this->attributes = [];
        $this->requiredAssets = [];
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->hasAttribute($name) ? $this->attributes[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @param string $name
     */
    public function requireAsset($name)
    {
        // TODO Move this management to AssetRequirement object
        $asset = $this->engine->getAsset($name);
        if (!$asset) {
            throw new \UnexpectedValueException('No such asset: ' . $name);
        }
        $this->requiredAssets[$asset->getName()] = $asset;
    }

    /**
     * @param string $stage
     * @return array
     */
    public function assetUrls($stage = null)
    {
        return $this->engine->assetUrlsOf($this->requiredAssets, $stage);
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
     * @param string $folderName
     * @param string $subPath
     */
    public function setTemplateFolder($folderName, $subPath)
    {
        $this->engine->setTemplateFolder($folderName, $subPath);
    }

    /**
     * @param ResponseInterface $response
     * @param string $template
     * @param array $data
     * @return ResponseInterface
     */
    public function render(ResponseInterface $response, $template, array $data = [])
    {
        $content = $this->fetchTemplate($template, $data);
        $response->getBody()->write($content);
        return $response;
    }

    /**
     * @param string $name
     * @param array $data
     * @return string
     */
    public function fetchTemplate($name, array $data = [])
    {
        return $this->engine->fetchTemplateIn($this, $name, $data);
    }
}
