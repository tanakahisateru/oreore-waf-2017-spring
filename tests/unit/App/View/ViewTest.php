<?php
namespace Acme\App\View;

use Acme\App\Router\Router;
use Http\Factory\Diactoros\ResponseFactory;
use Lapaz\Amechan\AssetManager;
use League\Plates\Engine;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /**
     * @var callable
     */
    protected $templateEngineFactory;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var AssetManager
     */
    protected $assetManager;

    public function testRender()
    {
        $view = new View($this->templateEngineFactory, $this->router, $this->assetManager);
        $content = $view->render('/foo.php', [
            'param' => '>test',
        ]);

        $this->assertContains('&gt;test', $content);
    }

    public function testAttributes()
    {
        $view = new View($this->templateEngineFactory, $this->router, $this->assetManager);
        $view->setAttribute('foo', 'Foo');

        $this->assertTrue($view->hasAttribute('foo'));
        $this->assertFalse($view->hasAttribute('bar'));

        $this->assertEquals('Foo', $view->getAttribute('foo'));
        $this->assertEquals('n/a', $view->getAttribute('bar', 'n/a'));
    }

    public function testRenderWithFolder()
    {
        $view = new View($this->templateEngineFactory, $this->router, $this->assetManager);
        $view->setFolder('current', 'folder0');

        $this->assertTrue($view->hasFolder('current'));
        $this->assertFalse($view->hasFolder('not_set'));
        $this->assertEquals('folder0', $view->getFolder('current'));

        $content = $view->render('current::foo.php', [
            'param' => '>test',
        ]);

        $this->assertContains('in folder0', $content);

        $view->setFolder('current', 'folder1');
        $content = $view->render('current::foo.php', [
            'param' => '>test',
        ]);

        $this->assertContains('in folder1', $content);
    }

    public function testRenderWithSelfReference()
    {
        $view = new View($this->templateEngineFactory, $this->router, $this->assetManager);
        $view->setAttribute('alpha', 'Beta');
        $content = $view->render('/attr.php');

        $this->assertContains('Beta', $content);
    }

    protected function setUp()
    {
        $this->templateEngineFactory = function() {
            $engine = new Engine(__DIR__ . '/templates', null);
            return $engine;
        };

        $this->router = new Router([], new ResponseFactory());

        $this->assetManager = new AssetManager();
    }
}
