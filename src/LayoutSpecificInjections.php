<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer;

final class LayoutSpecificInjections
{
    /**
     * @var object[]
     */
    private readonly array $injections;

    public function __construct(
        private readonly string $layout,
        object ...$injections
    ) {
        $this->injections = $injections;
    }

    /**
     * @return object[]
     */
    public function getInjections(): array
    {
        return $this->injections;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }
}
