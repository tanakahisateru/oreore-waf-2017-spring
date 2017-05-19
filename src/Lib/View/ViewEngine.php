<?php
namespace My\Web\Lib\View;

use Lapaz\Amechan\AssetCollection;
use Lapaz\Amechan\AssetManager;
use My\Web\Lib\Event\Interceptor;
use My\Web\Lib\Event\InterceptorException;
use My\Web\Lib\Router\Router;
use My\Web\Lib\View\Template\TemplateEngine;
use Webmozart\PathUtil\Path;

class ViewEngine
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var TemplateEngine
     */
    protected $templateEngine;

    /**
     * @var AssetManager
     */
    protected $assetManager;

    /**
     * @var callable
     */
    protected $viewFactory;

    /**
     * View constructor.
     * @param Router $router
     * @param TemplateEngine $templateEngine
     * @param AssetManager $assetManager
     * @param callable $viewFactory
     */
    public function __construct(
        Router $router,
        TemplateEngine $templateEngine,
        AssetManager $assetManager,
        callable $viewFactory
    )
    {
        $this->router = $router;
        $this->templateEngine = $templateEngine;
        $this->assetManager = $assetManager;
        $this->viewFactory = $viewFactory;
    }

    /**
     * @return View
     */
    public function createView()
    {
        return call_user_func($this->viewFactory, $this);
    }

    /**
     * @return AssetCollection
     */
    public function createAssetCollection()
    {
        return $this->assetManager->newCollection();
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
            return $this->router->rawUrlTo($name, $data);
        } else {
            return $this->router->urlTo($name, $data);
        }
    }

    /**
     * @param string $url
     * @return string
     */
    public function resourceUrlTo($url)
    {
        return $this->assetManager->url($url);
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
        $engine = clone $this->templateEngine;
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
