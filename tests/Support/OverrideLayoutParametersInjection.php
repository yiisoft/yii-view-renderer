<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests\Support;

use Yiisoft\Yii\View\CommonParametersInjectionInterface;
use Yiisoft\Yii\View\LayoutParametersInjectionInterface;

final class OverrideLayoutParametersInjection implements
    CommonParametersInjectionInterface,
    LayoutParametersInjectionInterface
{
    public function getCommonParameters(): array
    {
        return [
            'seoTitle' => 'COMMON',
        ];
    }

    public function getLayoutParameters(): array
    {
        return [
            'seoTitle' => 'LAYOUT',
        ];
    }
}
