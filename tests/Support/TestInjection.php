<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests\Support;

use Yiisoft\Html\Html;
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
            'favicon' => Html::link('/icon.png', [
                'rel' => 'icon',
                'type' => 'image/png',
            ]),
        ];
    }

    public function getMetaTags(): array
    {
        return [
            'description' => Html::meta([
                'name' => 'description',
                'content' => 'This website is about funny raccoons.',
            ]),
        ];
    }
}
