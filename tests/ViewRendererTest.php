<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer\Tests;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\StreamFactory;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use RuntimeException;
use stdClass;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\Renderer\Exception\InvalidLinkTagException;
use Yiisoft\Yii\View\Renderer\Exception\InvalidMetaTagException;
use Yiisoft\Yii\View\Renderer\InjectionContainer\InjectionContainer;
use Yiisoft\Yii\View\Renderer\InjectionContainer\InjectionContainerInterface;
use Yiisoft\Yii\View\Renderer\LayoutSpecificInjections;
use Yiisoft\Yii\View\Renderer\MetaTagsInjectionInterface;
use Yiisoft\Yii\View\Renderer\Tests\Support\CharsetInjection;
use Yiisoft\Yii\View\Renderer\Tests\Support\FakeCntrl;
use Yiisoft\Yii\View\Renderer\Tests\Support\FakeController;
use Yiisoft\Yii\View\Renderer\Tests\Support\InvalidLinkTagInjection;
use Yiisoft\Yii\View\Renderer\Tests\Support\InvalidPositionInLinkTagInjection;
use Yiisoft\Yii\View\Renderer\Tests\Support\InvalidMetaTagInjection;
use Yiisoft\Yii\View\Renderer\Tests\Support\CommonParametersInjection;
use Yiisoft\Yii\View\Renderer\Tests\Support\LayoutParametersInjection;
use Yiisoft\Yii\View\Renderer\Tests\Support\TestInjection;
use Yiisoft\Yii\View\Renderer\Tests\Support\TestTrait;
use Yiisoft\Yii\View\Renderer\Tests\Support\TitleInjection;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class ViewRendererTest extends TestCase
{
    use TestTrait;

    public function testRenderAndRenderAsString(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withLayout('@views/with-injection/layout.php')
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

        $this->assertEqualStringsIgnoringLineEndings($expected, (string) $response->getBody());

        $this->assertEqualStringsIgnoringLineEndings(
            $expected,
            $renderer->renderAsString('view', [
                'name' => 'donatello',
            ])
        );
    }

    public function testRenderWithAbsoluteLayoutPath(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withLayout($this->getViewsDir() . '/layout.php');

        $response = $renderer->render('simple', [
            'name' => 'donatello',
        ]);

        $this->assertSame('<html><body><b>donatello</b></body></html>', (string) $response->getBody());
    }

    public function testRenderAsStringWithAbsoluteLayoutPath(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withLayout($this->getViewsDir() . '/layout.php');

        $result = $renderer->renderAsString('simple', [
            'name' => 'donatello',
        ]);

        $this->assertSame('<html><body><b>donatello</b></body></html>', $result);
    }

    public function testRenderWithoutLayout(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withLayout(null)
            ->withInjections(new TestInjection());

        $response = $renderer->render('simple');

        $this->assertSame('<b>leonardo</b>', (string) $response->getBody());

        $response = $renderer->render('simple', [
            'name' => 'donatello',
        ]);

        $this->assertSame('<b>donatello</b>', (string) $response->getBody());
    }

    public function testRenderAsStringWithoutLayout(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withLayout(null)
            ->withInjections(new TestInjection());

        $result = $renderer->renderAsString('simple');

        $this->assertSame('<b>leonardo</b>', $result);

        $result = $renderer->renderAsString('simple', [
            'name' => 'donatello',
        ]);

        $this->assertSame('<b>donatello</b>', $result);
    }

    public function testRenderPartial(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withInjections(new TestInjection());

        $response = $renderer->renderPartial('simple');

        $this->assertSame('<b>leonardo</b>', (string) $response->getBody());

        $renderer = $renderer->withLayout(null);

        $response = $renderer->renderPartial('simple', [
            'name' => 'donatello',
        ]);

        $this->assertSame('<b>donatello</b>', (string) $response->getBody());
    }

    public function testRenderPartialAsString(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withInjections(new TestInjection());

        $result = $renderer->renderPartialAsString('simple');

        $this->assertSame('<b>leonardo</b>', $result);

        $renderer = $renderer->withLayout(null);

        $result = $renderer->renderPartialAsString('simple', [
            'name' => 'donatello',
        ]);

        $this->assertSame('<b>donatello</b>', $result);
    }

    public function testRenderWithLocale(): void
    {
        $renderer = $this->getRenderer()
            ->withInjections(new TestInjection());

        $response = $renderer
            ->withLocale('de_DE')
            ->render('locale');

        $this->assertSame('<html><body>de_DE locale</body></html>', (string) $response->getBody());
    }

    public static function dataWithController(): array
    {
        require_once __DIR__ . '/Support/RootNamespace/FakeController.php';
        require_once __DIR__ . '/Support/RootNamespace/Fake8Controller.php';

        return [
            'controller name, no "controller" / "controllers" namespaces, no subnamespaces' => [
                new Support\FakeController(),
                '/fake',
            ],
            'controller name, "controller" namespace, 1 subnamespace' => [
                new Support\Controller\SubNamespace\FakeController(),
                '/sub-namespace/fake',
            ],
            'controller name, "controllers" namespace, 1 subnamespace' => [
                new Support\Controllers\SubNamespace\FakeController(),
                '/sub-namespace/fake',
            ],
            'controller name, "controller" namespace, 2 subnamespaces' => [
                new Support\Controller\SubNamespace\SubNamespace2\FakeController(),
                '/sub-namespace/sub-namespace2/fake',
            ],
            'controller name, "controllers" namespace, 2 subnamespaces' => [
                new Support\Controllers\SubNamespace\SubNamespace2\FakeController(),
                '/sub-namespace/sub-namespace2/fake',
            ],
            'controller name, without "controller" / "controllers" namespaces, subnamespaces' => [
                new Support\NotCntrls\SubNamespace\FakeController(),
                '/fake',
            ],
            'controller name with root namespace' => [
                new \FakeController(),
                '/fake',
            ],
            'controller class contains number' => [
                new \Fake8Controller(),
                '/fake8',
            ],
            'namespace contains number' => [
                new Support\Controller\Sub8Namespace\FakeController(),
                '/sub8namespace/fake',
            ],
            'several controller in namespace' => [
                new Support\AllControllers\MoreController\MyController(),
                '/my',
            ],
            'several controller in namespace, nested' => [
                new Support\AllControllers\MoreController\Nested\MyController(),
                '/nested/my',
            ],
        ];
    }

    #[DataProvider('dataWithController')]
    public function testWithController(object $controller, string $path): void
    {
        $renderer = $this->getRenderer()->withController($controller);

        $this->assertSame($this->getViewsDir() . $path, $renderer->getViewPath());
    }

    public function testTwiceWithController(): void
    {
        $controller = new FakeController();

        $renderer = $this
            ->getRenderer()
            ->withController($controller)
            ->withController($controller); // twice for test of cache

        $this->assertSame($this->getViewsDir() . '/fake', $renderer->getViewPath());
    }

    public static function dataWithIncorrectController(): array
    {
        return [
            'root namespace' => [new stdClass()],
            'with namespace' => [new FakeCntrl()],
            'with controllers namespace' => [new Support\Controllers\FakeCntrl()],
            'with controller namespace and subnamespace, classname not ends with "Controller"' => [
                new Support\Controller\SubNamespace\SubController\FakeCntrl(),
            ],
        ];
    }

    #[DataProvider('dataWithIncorrectController')]
    public function testWithIncorrectController(object $controller): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot detect controller name.');
        $this
            ->getRenderer()
            ->withController($controller);
    }

    public function testWithViewPath(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withViewPath('/dir/');

        $this->assertSame('/dir', $renderer->getViewPath());
    }

    public function testWithViewPathWithAlias(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withViewPath('@views/dir');

        $this->assertSame($this->getViewsDir() . '/dir', $renderer->getViewPath());
    }

    public function testWithViewPathWithController(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withViewPath('/dir//')
            ->withController(new FakeController());

        $this->assertSame('/dir/fake', $renderer->getViewPath());
    }

    public function testInvalidMetaTag(): void
    {
        $renderer = $this
            ->getRenderer()
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
        $renderer = $this
            ->getRenderer()
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
        $renderer = $this
            ->getRenderer()
            ->withInjections(new InvalidPositionInLinkTagInjection());

        $response = $renderer->render('empty');

        $this->expectException(InvalidLinkTagException::class);
        $this->expectExceptionMessage(
            'Link tag position in injection should be integer. Got string.',
        );
        $response->getBody();
    }

    public function testCommonParametersInjectionsToNestedViews(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withLayout(null)
            ->withInjections(new TestInjection());

        $response = $renderer->render('nested/root', ['label' => 'root']);

        $this->assertSame('root: leonardo. nested-1: leonardo. nested-2: leonardo.', (string) $response->getBody());
    }

    public function testLayoutParametersInjectionsToNestedViews(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withLayout('@views/nested-layout/layout.php')
            ->withInjections(new TitleInjection());

        $response = $renderer->render('empty');

        $this->assertSame(
            '<html><head><title>Hello</title></head><body><h1>Hello</h1></body></html>',
            (string) $response->getBody(),
        );
    }

    public function testChangeInjectionsAfterCreateProxyAndBeforeRender(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withLayout('@views/with-injection/layout.php')
            ->withControllerName('with-injection')
            ->withInjections(new TestInjection());

        $response = $renderer->render('view', [
            'name' => 'donatello',
        ]);

        $injectionsProperty = (new ReflectionObject($renderer))->getProperty('injections');
        $injectionsProperty->setAccessible(true);
        $injectionsProperty->setValue($renderer, []);

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

        $this->assertEqualStringsIgnoringLineEndings($expected, (string) $response->getBody());
    }

    public function testPassingCommonParametersFromContentToLayout(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withViewPath('@views/passing-parameters-to-layout')
            ->withLayout('@views/passing-parameters-to-layout/layout.php');

        $response = $renderer->render('content', [
            'h1' => 'HELLO',
        ]);

        $expected = '<html><head><title>TITLE / HELLO</title></head><body><h1>HELLO</h1></body></html>';

        $this->assertEqualStringsIgnoringLineEndings($expected, (string) $response->getBody());
    }

    public function testCommonParametersOverrideLayout(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withLayout('@views/override-layout-parameters/layout.php')
            ->withInjections(new CommonParametersInjection());

        $response = $renderer->render('empty');

        $expected = '<html><head><title>COMMON</title></head><body></body></html>';

        $this->assertEqualStringsIgnoringLineEndings($expected, (string) $response->getBody());
    }

    public function testInRenderSetParametersOverrideLayout(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withViewPath('@views/override-layout-parameters')
            ->withLayout('@views/override-layout-parameters/layout.php')
            ->withInjections(new CommonParametersInjection(), new LayoutParametersInjection());

        $response = $renderer->render('content');

        $expected = '<html><head><title>RENDER</title></head><body></body></html>';

        $this->assertEqualStringsIgnoringLineEndings($expected, (string) $response->getBody());
    }

    public function testRenderParametersNotOverrideLayout(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withLayout('@views/override-layout-parameters/layout.php')
            ->withInjections(new LayoutParametersInjection());

        $response = $renderer->render('empty', ['seoTitle' => 'custom']);

        $expected = '<html><head><title>LAYOUT</title></head><body></body></html>';

        $this->assertEqualStringsIgnoringLineEndings($expected, (string) $response->getBody());
    }

    public function testWithoutViewPath(): void
    {
        $viewRenderer = new ViewRenderer(
            new DataResponseFactory(new ResponseFactory(), new StreamFactory()),
            new Aliases(),
            new WebView(__DIR__, new SimpleEventDispatcher()),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The view path is not set.');
        $viewRenderer->getViewPath();
    }

    public function testLazyLoadingInjection(): void
    {
        $container = new SimpleContainer([
            CharsetInjection::class => new CharsetInjection(),
        ]);

        $renderer = $this
            ->getRenderer(injectionContainer: new InjectionContainer($container))
            ->withLayout('@views/simple/layout.php')
            ->withControllerName('simple')
            ->withInjections(CharsetInjection::class);

        $response = $renderer->render('view');

        $expected = <<<'EOD'
<html>
<head><meta charset="utf-8"></head>
<body>
content</body>
</html>
EOD;

        $this->assertEqualStringsIgnoringLineEndings($expected, (string) $response->getBody());
    }

    public function testLazyLoadingInjectionWithoutContainer(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withLayout('@views/simple/layout.php')
            ->withControllerName('simple')
            ->withInjections(CharsetInjection::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Injections container is not set.');
        $renderer->render('view');
    }

    public function testLayoutSpecificInjections(): void
    {
        $renderer = $this
            ->getRenderer()
            ->withLayout('@views/nested-layout/layout.php')
            ->withInjections(
                new LayoutSpecificInjections(
                    '@views/nested-layout/layout.php',
                    new TitleInjection(),
                ),
                new LayoutSpecificInjections(
                    '@views/layout.php',
                    new TestInjection(),
                ),
                new class () implements MetaTagsInjectionInterface {
                    public function getMetaTags(): array
                    {
                        return [
                            ['charset' => 'windows-1251'],
                        ];
                    }
                }
            );

        $response = $renderer->render('empty');

        $this->assertSame(
            '<html><head><title>Hello</title><meta charset="windows-1251"></head><body><h1>Hello</h1></body></html>',
            (string) $response->getBody(),
        );
    }

    public function testImmutability(): void
    {
        $original = $this->getRenderer();
        $this->assertNotSame($original, $original->withController(new FakeController()));
        $this->assertNotSame($original, $original->withControllerName('test'));
        $this->assertNotSame($original, $original->withViewPath(''));
        $this->assertNotSame($original, $original->withLayout(''));
        $this->assertNotSame($original, $original->withAddedInjections());
        $this->assertNotSame($original, $original->withInjections());
    }

    private function getRenderer(
        ?InjectionContainerInterface $injectionContainer = null,
    ): ViewRenderer {
        return new ViewRenderer(
            new DataResponseFactory(new ResponseFactory(), new StreamFactory()),
            new Aliases(['@views' => $this->getViewsDir()]),
            new WebView('@views', new SimpleEventDispatcher()),
            '@views',
            '@views/layout.php',
            injectionContainer: $injectionContainer
        );
    }

    private function getViewsDir(): string
    {
        return __DIR__ . '/views';
    }
}
