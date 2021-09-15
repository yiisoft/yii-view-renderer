<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests\Support;

use Yiisoft\Yii\View\CommonParametersInjectionInterface;

final class CommonParametersInjection implements CommonParametersInjectionInterface
{
    public function getCommonParameters(): array
    {
        return [
            'seoTitle' => 'COMMON',
        ];
    }
}
