<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\ViewRenderer;

final class ConfigTest extends TestCase
{
    public function testDiWeb(): void
    {
        $container = $this->createContainer('web');

        $viewRenderer = $container->get(ViewRenderer::class);

        $this->assertInstanceOf(ViewRenderer::class, $viewRenderer);
    }

    public function testEventsWebWithDebug(): void
    {
        $eventsConfig = $this->getEventsWeb(['yiisoft/yii-debug' => ['enabled' => true]]);
        $this->assertCount(1, $eventsConfig);
    }

    public function testEventsWebWithoutDebug(): void
    {
        $eventsConfig = $this->getEventsWeb(['yiisoft/yii-debug' => ['enabled' => false]]);
        $this->assertCount(0, $eventsConfig);
    }

    private function createContainer(?string $postfix = null): Container
    {
        return new Container(
            ContainerConfig::create()->withDefinitions(
                $this->getDiConfig($postfix)
                +
                [
                    DataResponseFactoryInterface::class => $this->createMock(DataResponseFactoryInterface::class),
                    WebView::class => new WebView(__DIR__, new SimpleEventDispatcher()),
                ]
            )
        );
    }

    private function getDiConfig(?string $postfix = null): array
    {
        $params = $this->getParams();
        return require dirname(__DIR__) . '/config/di' . ($postfix !== null ? '-' . $postfix : '') . '.php';
    }

    private function getEventsWeb(?array $params = null)
    {
        $params ??= $this->getParams();
        return require dirname(__DIR__) . '/config/events-web.php';
    }

    private function getParams(): array
    {
        return require dirname(__DIR__) . '/config/params.php';
    }
}
