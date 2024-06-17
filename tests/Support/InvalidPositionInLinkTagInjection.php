<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer\Tests\Support;

use Yiisoft\Yii\View\Renderer\LinkTagsInjectionInterface;

final class InvalidPositionInLinkTagInjection implements LinkTagsInjectionInterface
{
    public function getLinkTags(): array
    {
        return [
            [
                'data',
                '__position' => 'end',
            ],
        ];
    }
}
