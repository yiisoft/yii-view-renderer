<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer\Tests\Debug;

use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\View\Event\WebView\AfterRender;
use Yiisoft\View\WebView;
use Yiisoft\Yii\Debug\Collector\CollectorInterface;
use Yiisoft\Yii\Debug\Tests\Shared\AbstractCollectorTestCase;
use Yiisoft\Yii\View\Renderer\Debug\WebViewCollector;

final class WebViewCollectorTest extends AbstractCollectorTestCase
{
    /**
     * @param CollectorInterface|WebViewCollector $collector
     */
    protected function collectTestData(CollectorInterface|WebViewCollector $collector): void
    {
        $collector->collect(new AfterRender(new WebView(__DIR__, $this->createMock(EventDispatcherInterface::class)), __FILE__, ['foo' => 'bar'], 'test content'));
    }

    protected function getCollector(): CollectorInterface
    {
        return new WebViewCollector();
    }

    protected function checkCollectedData(array $data): void
    {
        $this->assertSame(
            [
                [
                    'output' => 'test content',
                    'file' => __FILE__,
                    'parameters' => ['foo' => 'bar'],
                ],
            ],
            $data,
        );
    }

    protected function checkSummaryData(array $data): void
    {
        $this->assertSame(['total' => 1], $data);
    }
}
