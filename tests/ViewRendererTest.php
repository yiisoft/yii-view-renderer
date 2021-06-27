<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\StreamFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\Exception\InvalidLinkTagException;
use Yiisoft\Yii\View\Exception\InvalidMetaTagException;
use Yiisoft\Yii\View\Tests\Support\FakeController;
use Yiisoft\Yii\View\Tests\Support\InvalidLinkTagInjection;
use Yiisoft\Yii\View\Tests\Support\InvalidPositionInLinkTagInjection;
use Yiisoft\Yii\View\Tests\Support\InvalidMetaTagInjection;
use Yiisoft\Yii\View\Tests\Support\TestInjection;
use Yiisoft\Yii\View\Tests\Support\TestTrait;
use Yiisoft\Yii\View\ViewRenderer;

final class ViewRendererTest extends TestCase
{
    use TestTrait;

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
<head><meta charset="utf-8">
<meta name="description" content="This website is about funny raccoons.">
<link type="image/png" href="/icon.png" rel="icon">
<link type="font/woff2" href="myFont.woff2" rel="preload" as="font"></head>
<body>
    <p><b>donatello</b></p>
    <div>copyright</div>
    <link href="fancy.css" rel="alternate stylesheet"></body>
</html>
EOD;

        $this->assertEqualStringsIgnoringLineEndings($expected, (string)$response->getBody());
    }

    public function testRenderWithFullPathLayout(): void
    {
        $renderer = $this->getRenderer()->withLayout($this->getViewsDir() . '/layout.php');

        $response = $renderer->render('simple', [
            'name' => 'donatello',
        ]);

        $this->assertSame('<html><body><b>donatello</b></body></html>', (string) $response->getBody());
    }

    public function testRenderWithoutLayout(): void
    {
        $renderer = $this->getRenderer()->withLayout(null);

        $response = $renderer->render('simple', [
            'name' => 'donatello',
        ]);

        $this->assertSame('<b>donatello</b>', (string) $response->getBody());
    }

    public function testRenderPartial(): void
    {
        $renderer = $this->getRenderer();

        $response = $renderer->renderPartial('simple', [
            'name' => 'donatello',
        ]);

        $this->assertSame('<b>donatello</b>', (string) $response->getBody());
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
        $this->expectExceptionMessage('Cannot detect controller name.');
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

    public function testInvalidMetaTag(): void
    {
        $renderer = $this->getRenderer()
            ->withInjections(new InvalidMetaTagInjection());

        $response = $renderer->render('empty');

        $this->expectException(InvalidMetaTagException::class);
        $this->expectExceptionMessage(
            'Meta tag in injection should be instance of Yiisoft\Html\Tag\Meta or an array. Got string.',
        );
        $response->getBody();
    }

    public function testInvalidLinkTag(): void
    {
        $renderer = $this->getRenderer()
            ->withInjections(new InvalidLinkTagInjection());

        $response = $renderer->render('empty');

        $this->expectException(InvalidLinkTagException::class);
        $this->expectExceptionMessage(
            'Link tag in injection should be instance of Yiisoft\Html\Tag\Link or an array. Got string.',
        );
        $response->getBody();
    }

    public function testInvalidPositionInLinkTag(): void
    {
        $renderer = $this->getRenderer()
            ->withInjections(new InvalidPositionInLinkTagInjection());

        $response = $renderer->render('empty');

        $this->expectException(InvalidLinkTagException::class);
        $this->expectExceptionMessage(
            'Link tag position in injection should be integer. Got string.',
        );
        $response->getBody();
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
            new DataResponseFactory(new ResponseFactory(), new StreamFactory()),
            new Aliases(['@views' => $this->getViewsDir()]),
            new WebView('@views', new SimpleEventDispatcher()),
            '@views',
            '@views/layout'
        );
    }

    private function getViewsDir(): string
    {
        return __DIR__ . '/views';
    }
}
