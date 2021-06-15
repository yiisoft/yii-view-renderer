<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests\Support;

use Yiisoft\Yii\View\LinkTagsInjectionInterface;

final class InvalidLinkTagInjection implements LinkTagsInjectionInterface
{
    public function getLinkTags(): array
    {
        return [
            'data',
        ];
    }
}
