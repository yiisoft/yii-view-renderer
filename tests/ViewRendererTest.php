<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;
use stdClass;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\Tests\Support\FakeController;
use Yiisoft\Yii\View\Tests\Support\TestInjection;
use Yiisoft\Yii\View\ViewRenderer;

final class ViewRendererTest extends TestCase
{
    public function testRender(): void
    {
        $renderer = $this->getRenderer()
            ->withLayout('@views/with-injection/layout')
            ->withControllerName('with-injection')
            ->withInjections(new TestInjection());

        $response = $renderer->render('view', [
            'name' => 'donatello',
        ]);

        $expected = <<<'EOD'
<html>
<head><meta name="description" content="This website is about funny raccoons.">
<link type="image/png" href="/icon.png" rel="icon"></head>
<body>
    <p><b>donatello</b></p>
    <div>copyright</div>
    </body>
</html>
EOD;

        $this->assertSameWithoutLE($expected, (string)$response->getBody());
    }

    public function testRenderWithFullPathLayout(): void
    {
        $renderer = $this->getRenderer()->withLayout($this->getViewsDir() . '/layout.php');

        $response = $renderer->render('simple', [
            'name' => 'donatello',
        ]);

        $this->assertSame(
            '<html><body><b>donatello</b></body></html>',
            (string)$response->getBody()
        );
    }

    public function testRenderWithoutLayout(): void
    {
        $renderer = $this->getRenderer()->withLayout(null);

        $response = $renderer->render('simple', [
            'name' => 'donatello',
        ]);

        $this->assertSame(
            '<b>donatello</b>',
            (string)$response->getBody()
        );
    }

    public function testRenderPartial(): void
    {
        $renderer = $this->getRenderer();

        $response = $renderer->renderPartial('simple', [
            'name' => 'donatello',
        ]);

        $this->assertSame(
            '<b>donatello</b>',
            (string)$response->getBody()
        );
    }

    public function testWithController(): void
    {
        $controller = new FakeController();

        $renderer = $this->getRenderer()
            ->withController($controller)
            ->withController($controller); // twice for test of cache

        $this->assertSame($this->getViewsDir() . '/support/fake', $renderer->getViewPath());
    }

    public function testWithIncorrectController(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot detect controller name');
        $this->getRenderer()->withController(new stdClass());
    }

    public function testWithViewPath(): void
    {
        $renderer = $this->getRenderer()->withViewPath('/dir/');

        $this->assertSame('/dir/', $renderer->getViewPath());
    }

    public function testWithViewPathWithAlias(): void
    {
        $renderer = $this->getRenderer()->withViewPath('@views/dir');

        $this->assertSame($this->getViewsDir() . '/dir', $renderer->getViewPath());
    }

    public function testImmutability(): void
    {
        $original = $this->getRenderer();
        $this->assertNotSame($original, $original->withController(new FakeController()));
        $this->assertNotSame($original, $original->withControllerName('test'));
        $this->assertNotSame($original, $original->withViewPath(''));
        $this->assertNotSame($original, $original->withViewBasePath(''));
        $this->assertNotSame($original, $original->withLayout(''));
        $this->assertNotSame($original, $original->withAddedInjections());
        $this->assertNotSame($original, $original->withInjections());
    }

    private function getRenderer(): ViewRenderer
    {
        return new ViewRenderer(
            new DataResponseFactory(
                new Psr17Factory()
            ),
            new Aliases(['@views' => $this->getViewsDir()]),
            new WebView(
                '@views',
                new SimpleEventDispatcher(),
                new NullLogger()
            ),
            '@views',
            '@views/layout'
        );
    }

    private function getViewsDir(): string
    {
        return __DIR__ . '/views';
    }

    /**
     * Asserting two strings equality ignoring line endings.
     *
     * @param string $expected
     * @param string $actual
     * @param string $message
     */
    public function assertSameWithoutLE(string $expected, string $actual, string $message = ''): void
    {
        $expected = str_replace("\r\n", "\n", $expected);
        $actual = str_replace("\r\n", "\n", $actual);
        $this->assertSame($expected, $actual, $message);
    }
}
