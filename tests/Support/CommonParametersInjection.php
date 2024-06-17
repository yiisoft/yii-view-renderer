<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer\Tests\Support;

use Yiisoft\Yii\View\Renderer\CommonParametersInjectionInterface;

final class CommonParametersInjection implements CommonParametersInjectionInterface
{
    public function getCommonParameters(): array
    {
        return [
            'seoTitle' => 'COMMON',
        ];
    }
}
