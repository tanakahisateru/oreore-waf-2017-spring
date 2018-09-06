<?php

namespace Acme\App\View;

use Acme\App\Router\Router;
use Lapaz\Amechan\AssetManager;

class ViewFactory
{
    /**
     * @var callable
     */
    private $templateEngineFactory;
    /**
     * @var AssetManager
     */
    private $assetManager;


    /**
     * ViewFactory constructor.
     * @param callable $templateEngineFactory
     * @param AssetManager $assetManager
     */
    public function __construct(callable $templateEngineFactory, AssetManager $assetManager)
    {
        $this->templateEngineFactory = $templateEngineFactory;
        $this->assetManager = $assetManager;
    }

    /**
     * @param Router $router
     * @return View
     */
    public function createView(Router $router): View
    {
        return new View($this->templateEngineFactory, $router, $this->assetManager);
    }
}