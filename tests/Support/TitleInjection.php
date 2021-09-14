<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests\Support;

use Yiisoft\Yii\View\LayoutParametersInjectionInterface;

final class TitleInjection implements LayoutParametersInjectionInterface
{
    public function getLayoutParameters(): array
    {
        return ['title' => 'Hello'];
    }
}
