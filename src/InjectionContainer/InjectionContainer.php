<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\InjectionContainer;

use Psr\Container\ContainerInterface;

final class InjectionContainer implements InjectionContainerInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    public function get(string $id): object
    {
        /** @var object */
        return $this->container->get($id);
    }
}
