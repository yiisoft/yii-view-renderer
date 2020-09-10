<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Csrf;

final class CsrfToken implements CsrfTokenInterface
{

    public function get(): string
    {
        return (string)\Yiisoft\Csrf\CsrfToken::getValue();
    }
}
