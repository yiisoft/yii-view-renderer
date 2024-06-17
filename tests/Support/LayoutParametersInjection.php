<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer\Tests\Support;

use Yiisoft\Yii\View\Renderer\LayoutParametersInjectionInterface;

final class LayoutParametersInjection implements LayoutParametersInjectionInterface
{
    public function getLayoutParameters(): array
    {
        return [
            'seoTitle' => 'LAYOUT',
        ];
    }
}
