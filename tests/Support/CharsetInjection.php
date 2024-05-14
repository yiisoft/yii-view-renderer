<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests\Support;

use Yiisoft\Yii\View\MetaTagsInjectionInterface;

final class CharsetInjection implements MetaTagsInjectionInterface
{
    public function getMetaTags(): array
    {
        return [
            ['charset' => 'utf-8'],
        ];
    }
}
