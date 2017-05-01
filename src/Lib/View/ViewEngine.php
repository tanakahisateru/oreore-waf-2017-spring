<?php
namespace My\Web\Lib\View;

use League\Plates\Engine;
use My\Web\Lib\Event\Interceptor;
use My\Web\Lib\Event\InterceptorException;
use My\Web\Lib\Router\Router;
use My\Web\Lib\View\Asset\AssetManager;
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
     * @param string $class
     * @return View
     */
    public function createView($class = View::class)
    {
        $factory = $this->container->get('viewFactory');
        return $factory($this, $this->getAssetManager(), $class);
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
     * @param string $templateName
     * @param array $data
     * @return string
     */
    public function renderIn(View $view, $templateName, array $data = [])
    {
        // Plate engine is stateful
        $engine = clone $this->getTemplateEngine();
        $rootPath = $engine->getDirectory();

        $engine->registerFunction('view', function () use ($view) {
            return $view;
        });

        foreach ($view->getFolderMap() as $folder => $path) {
            if ($engine->getFolders()->exists($folder)) {
                $engine->removeFolder($folder);
            }
            $engine->addFolder($folder, Path::join($rootPath, $path));
        }

        $template = $engine->make($templateName);

        $interceptor = Interceptor::createForEventCapable($view, function ($last, $argv) {
            if (isset($argv['content'])) {
                return $argv['content'];
            } elseif ($last) {
                return $last;
            } else {
                return "";
            }
        });

        try {
            $interceptor->trigger('beforeRender', $view, [
                'template' => $template,
                'data' => $data,
            ]);

            $result = $template->render($data);

            $interceptor->trigger('afterRender', $view, [
                'content' => $result,
                'data' => $data,
            ]);

            return $result;
        } catch (InterceptorException $e) {
            return $e->getLastResult();
        } finally {
            $engine->dropFunction('view');
        }
    }
}
