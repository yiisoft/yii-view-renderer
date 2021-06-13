<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests\Support;

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\ContentParametersInjectionInterface;
use Yiisoft\Yii\View\LayoutParametersInjectionInterface;
use Yiisoft\Yii\View\LinkTagsInjectionInterface;
use Yiisoft\Yii\View\MetaTagsInjectionInterface;

final class TestInjection implements
    ContentParametersInjectionInterface,
    LayoutParametersInjectionInterface,
    LinkTagsInjectionInterface,
    MetaTagsInjectionInterface
{
    public function getContentParameters(): array
    {
        return ['user' => 'leonardo'];
    }

    public function getLayoutParameters(): array
    {
        return ['footer' => 'copyright'];
    }

    public function getLinkTags(): array
    {
        return [
            'favicon' => Html::link(
                '/icon.png',
                [
                    'rel' => 'icon',
                    'type' => 'image/png',
                ]
            ),
            [
                'rel' => 'preload',
                'href' => "myFont.woff2",
                'as' => 'font',
                'type' => 'font/woff2'
            ],
            [
                Html::link('fancy.css', ['rel' => 'alternate stylesheet']),
                '__position' => WebView::POSITION_END,
            ],
        ];
    }

    public function getMetaTags(): array
    {
        return [
            ['charset' => 'utf-8'],
            'description' => Html::meta([
                'name' => 'description',
                'content' => 'This website is about funny raccoons.',
            ]),
        ];
    }
}
