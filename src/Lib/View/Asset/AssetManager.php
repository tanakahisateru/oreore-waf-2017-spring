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
    protected $mapping = [];

    /**
     * @var array
     */
    protected $revManifest = [];


    /**
     * @param string $name
     * @param AssetInterface $asset
     */
    public function set($name, AssetInterface $asset)
    {
        $this->assets[$name] = $asset;
    }

    public function asset(array $definition = [])
    {
        $baseUrl = isset($definition['baseUrl']) ? $definition['baseUrl'] : '';

        $elements = isset($definition['elements']) ? $definition['elements'] : [];
        if (!is_array($elements)) {
            $elements = [$elements];
        }

        $stage = isset($definition['stage']) ? $definition['stage'] : null;

        $dependencies = [];
        if (isset($definition['dependencies'])) {
            if (!is_array($definition['dependencies'])) {
                $definition['dependencies'] = [$definition['dependencies']];
            }
            foreach ($definition['dependencies'] as $dependency) {
                if (!($dependency instanceof AssetInterface || is_scalar($dependency))) {
                    throw new \InvalidArgumentException('Asset dependency must be string or Asset object');
                }
                $dependencies[] = $dependency;
            }
        }

        $subset = [];
        if (isset($definition['subset'])) {
            if (!is_array($definition['subset'])) {
                throw new \InvalidArgumentException('Asset subset must be array');
            }
            foreach ($definition['subset'] as $asset) {
                if (!is_array($asset)) {
                    throw new \InvalidArgumentException('Asset subset must be array');
                }
                if (empty($asset['baseUrl']) || !is_string($asset['baseUrl'])) {
                    $asset['baseUrl'] = $baseUrl;
                }
                if (empty($asset['stage']) || !is_string($asset['stage'])) {
                    $asset['stage'] = $stage;
                }
                if (!isset($asset['dependencies'])) {
                    $asset['dependencies'] = [];
                }
                $asset['dependencies'] = array_merge($dependencies, $asset['dependencies']);

                $subset[] = $this->asset($asset);
            }
        }
        $dependencies = array_merge($dependencies, $subset);

        return new Asset($this, $baseUrl, $elements, $stage, $dependencies);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->assets[$name]);
    }

    /**
     * @param string $name
     * @return AssetInterface
     */
    public function get($name)
    {
        return $this->has($name) ? $this->assets[$name] : null;
    }

    /**
     * @param string $target
     * @param array $source
     * @param string $prefix
     */
    public function map($target, $source, $prefix = '')
    {
        if (!is_array($source)) {
            $source = [$source];
        }

        if (!empty($prefix)) {
            $target = $prefix . $target;
            $source = array_map(function ($s) use ($prefix) {
                return $prefix . $s;
            }, $source);
        }

        foreach ($source as $s) {
            $this->mapping[$s] = $target;
        }
    }

    /**
     * @param array $manifest
     * @param string $prefix
     */
    public function rev($manifest, $prefix = '')
    {
        if (!empty($prefix)) {
            $prefixedManifest = [];
            foreach ($manifest as $k => $v) {
                $prefixedManifest[$prefix . $k] = $prefix . $v;
            }
            $manifest = $prefixedManifest;
        }

        $this->revManifest = array_merge($this->revManifest, $manifest);
    }

    /**
     * @param string $path
     * @return string
     */
    public function toUrl($path)
    {
        $url = $path;
        if (isset($this->mapping[$url])) {
            $url = $this->mapping[$url];
        }
        if (isset($this->revManifest[$url])) {
            $url = $this->revManifest[$url];
        }
        return $url;
    }
}
