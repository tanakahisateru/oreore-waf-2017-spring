<?php
namespace My\Web\Lib\View\Asset;

use Psr\Container\ContainerInterface;

class AssetManager implements ContainerInterface
{
    /**
     * @var UrlCollectableInterface[]
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
     * @param array $definition
     */
    public function asset($name, array $definition = [])
    {
        $this->set($name, $this->newBundle($definition));
    }

    /**
     * @param string $name
     * @param UrlCollectableInterface $source
     */
    public function set($name, UrlCollectableInterface $source)
    {
        $this->assets[$name] = $source;
    }

    /**
     * @param array $config
     * @return ResourceBundle
     */
    public function newBundle(array $config = [])
    {
        $baseUrl = isset($config['baseUrl']) ? $config['baseUrl'] : '';

        if (isset($config['files'])) {
            $files = $config['files'];
        } elseif (isset($config['file'])) {
            $files = $config['file'];
        } else {
            $files = [];
        }
        if (!is_array($files)) {
            $files = [$files];
        }

        $section = isset($config['section']) ? $config['section'] : null;

        if (isset($config['dependencies'])) {
            $dependencies = $config['dependencies'];
        } elseif (isset($config['dependency'])) {
            $dependencies = $config['dependency'];
        } else {
            $dependencies = [];
        }
        if (!is_array($dependencies)) {
            $dependencies = [$dependencies];
        }
        foreach ($dependencies as $dependency) {
            if (!($dependency instanceof UrlCollectableInterface || is_scalar($dependency))) {
                throw new \InvalidArgumentException('Asset dependency must be string or Asset object');
            }
        }

        $bundles = [];
        if (isset($config['bundles'])) {
            if (!is_array($config['bundles'])) {
                throw new \InvalidArgumentException('Asset bundles must be array');
            }
            foreach ($config['bundles'] as $bundleConfig) {
                if (!is_array($bundleConfig)) {
                    throw new \InvalidArgumentException('Asset bundles definition must be array');
                }
                if (empty($bundleConfig['baseUrl']) || !is_string($bundleConfig['baseUrl'])) {
                    $bundleConfig['baseUrl'] = $baseUrl;
                }
                if (empty($bundleConfig['section']) || !is_string($bundleConfig['section'])) {
                    $bundleConfig['section'] = $section;
                }
                if (!isset($bundleConfig['dependencies']) && !isset($bundleConfig['dependency'])) {
                    $bundleConfig['dependencies'] = [];
                }
                $bundleConfig['dependencies'] = array_merge($dependencies, $bundleConfig['dependencies']);

                $bundles[] = $this->newBundle($bundleConfig);
            }
        }

        return new ResourceBundle($this, $baseUrl, $files, $section, array_merge($dependencies, $bundles));
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
     * @return UrlCollectableInterface
     */
    public function get($name)
    {
        return $this->has($name) ? $this->assets[$name] : null;
    }

    /**
     * @param string $prefix
     * @param array $mapping
     */
    public function map($prefix, array $mapping)
    {
        foreach ($mapping as $combined => $sources) {
            if (!is_array($sources)) {
                $sources = [$sources];
            }

            if (!empty($prefix)) {
                $combined = $prefix . $combined;
                $sources = array_map(function ($s) use ($prefix) {
                    return $prefix . $s;
                }, $sources);
            }

            foreach ($sources as $s) {
                $this->mapping[$s] = $combined;
            }
        }
    }

    /**
     * @param string $prefix
     * @param array $manifest
     */
    public function rev($prefix, array $manifest)
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
     * @param string $url
     * @return string
     */
    public function url($url)
    {
        if (isset($this->mapping[$url])) {
            $url = $this->mapping[$url];
        }
        if (isset($this->revManifest[$url])) {
            $url = $this->revManifest[$url];
        }
        return $url;
    }

    /**
     * @return AssetUsage
     */
    public function createUsage()
    {
        // should be locked here
        return new AssetUsage($this);
    }
}
