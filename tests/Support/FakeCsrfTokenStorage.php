<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests\Support;

use Yiisoft\Csrf\TokenStorage\CsrfTokenStorageInterface;

final class FakeCsrfTokenStorage implements CsrfTokenStorageInterface
{
    private ?string $token = null;

    public function get(): ?string
    {
        return $this->token;
    }

    public function set(string $token): void
    {
        $this->token = $token;
    }

    public function remove(): void
    {
        $this->token = null;
    }
}
