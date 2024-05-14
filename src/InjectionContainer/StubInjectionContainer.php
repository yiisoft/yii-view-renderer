<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\InjectionContainer;

use RuntimeException;

/**
 * @internal
 */
final class StubInjectionContainer implements InjectionContainerInterface
{
    public function get(string $id): object
    {
        throw new RuntimeException('Injections container is not set.');
    }
}
