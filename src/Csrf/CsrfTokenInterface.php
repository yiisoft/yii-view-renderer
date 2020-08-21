<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Csrf;

interface CsrfTokenInterface
{

    public function getToken(): string;

    public function setToken(string $token): void;
}
