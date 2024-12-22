<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer\Tests\ViewRenderer\RenderCombinations;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\StreamFactory;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\Html\Tag\Link;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\Renderer\LinkTagsInjectionInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class RenderCombinationsTest extends TestCase
{
    private const EXPECTED_VIEW = 'test';
    private const EXPECTED_CONTENT = <<<HTML
        <html>
        <head><link href="style.css" rel="stylesheet"></head>
        <body>
        test</body>
        </html>
        HTML;

    public function testRenderAfterRenderPartial(): void
    {
        $renderer = $this->createRenderer();

        $this->assertSame(self::EXPECTED_VIEW, $renderer->renderPartialAsString('view'));
        $this->assertSame(self::EXPECTED_CONTENT, (string) $renderer->render('view')->getBody());
    }

    public function testRenderPartialAfterRender(): void
    {
        $renderer = $this->createRenderer();

        $this->assertSame(self::EXPECTED_CONTENT, (string) $renderer->render('view')->getBody());
        $this->assertSame(self::EXPECTED_VIEW, $renderer->renderPartialAsString('view'));
    }

    public function testRenderTwice(): void
    {
        $renderer = $this->createRenderer();

        $this->assertSame(self::EXPECTED_CONTENT, (string) $renderer->render('view')->getBody());
        $this->assertSame(self::EXPECTED_CONTENT, (string) $renderer->render('view')->getBody());
    }

    private function createRenderer(): ViewRenderer
    {
        return new ViewRenderer(
            new DataResponseFactory(new ResponseFactory(), new StreamFactory()),
            new Aliases(),
            new WebView(),
            __DIR__,
            __DIR__ . '/layout.php',
            [
                new class() implements LinkTagsInjectionInterface {
                    public function getLinkTags(): array
                    {
                        return [Link::toCssFile('style.css')];
                    }
                }
            ]
        );
    }
}
