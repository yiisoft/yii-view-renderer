<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Csrf;

interface CsrfViewInjectionInterface
{
    public function withRequestAttribute(?string $requestAttribute = null): CsrfViewInjectionInterface;
}
