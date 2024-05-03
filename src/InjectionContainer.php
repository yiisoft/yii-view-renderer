<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

use Psr\Container\ContainerInterface;
use RuntimeException;

final class InjectionContainer implements InjectionContainerInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    public function get(string $id): object
    {
        $result = $this->container->get($id);
        return is_object($result)
            ? $result
            : throw new RuntimeException('Injection should be object, ' . get_debug_type($result) . ' given.');
    }
}
