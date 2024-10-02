<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer\InjectionContainer;

use Psr\Container\ContainerInterface;

final class InjectionContainer implements InjectionContainerInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function get(string $id): object
    {
        /** @var object */
        return $this->container->get($id);
    }
}
