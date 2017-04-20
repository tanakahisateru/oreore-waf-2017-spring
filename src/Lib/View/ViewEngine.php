<?php
namespace My\Web\Lib\View;

use League\Plates\Engine;
use My\Web\Lib\Router\Router;
use My\Web\Lib\View\Asset\AssetManager;
use My\Web\Lib\View\Asset\AssetUsage;
use Psr\Container\ContainerInterface;
use Webmozart\PathUtil\Path;

class ViewEngine
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * View constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Engine
     */
    protected function getTemplateEngine()
    {
        return $this->container->get('templateEngine');
    }

    /**
     * @return AssetManager
     */
    protected function getAssetManager()
    {
        return $this->container->get('assetManager');
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->container->get('router');
    }

    /**
     * @return View
     */
    public function createView()
    {
        return new View($this, new AssetUsage($this->getAssetManager()));
    }

    /**
     * @param string $name
     * @param array $data
     * @param bool $raw
     * @return bool
     */
    public function routeUrlTo($name, $data=[], $raw = false)
    {
        if ($raw) {
            return $this->getRouter()->rawUrlTo($name, $data);
        } else {
            return $this->getRouter()->urlTo($name, $data);
        }
    }

    /**
     * @param string $url
     * @return string
     */
    public function resourceUrlTo($url)
    {
        return $this->getAssetManager()->url($url);
    }

    /**
     * @param View $view
     * @param string $name
     * @param array $data
     * @return string
     */
    public function renderIn(View $view, $name, array $data = [])
    {
        // Plate engine is stateful
        $engine = clone $this->getTemplateEngine();
        $rootPath = $engine->getDirectory();

        $engine->registerFunction('view', function () use ($view) {
            return $view;
        });

        foreach ($view->getFolderMap() as $folder => $path) {
            $engine->addFolder($folder, Path::join($rootPath, $path));
        }

        return $engine->render($name, $data);
    }
}
