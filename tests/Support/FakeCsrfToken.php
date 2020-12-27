<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests\Support;

use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Security\Random;

final class FakeCsrfToken implements CsrfTokenInterface
{
    private string $token;

    public function __construct(?string $token = null)
    {
        $this->token = $token ?? Random::string();
    }

    public function getValue(): string
    {
        return $this->token;
    }

    public function validate(string $token): bool
    {
        return $token === $this->token;
    }
}
