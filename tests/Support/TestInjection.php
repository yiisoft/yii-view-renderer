<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests\Support;

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
            [
                '__key' => 'favicon',
                'rel' => 'icon',
                'type' => 'image/png',
                'href' => '/icon.png',
            ],
        ];
    }

    public function getMetaTags(): array
    {
        return [
            [
                '__key' => 'description',
                'name' => 'description',
                'content' => 'This website is about funny raccoons.',
            ],
        ];
    }
}
