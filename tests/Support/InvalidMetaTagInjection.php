<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer\Tests\Support;

use Yiisoft\Yii\View\Renderer\MetaTagsInjectionInterface;

final class InvalidMetaTagInjection implements MetaTagsInjectionInterface
{
    public function getMetaTags(): array
    {
        return [
            'charset',
        ];
    }
}
