<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer\InjectionContainer;

interface InjectionContainerInterface
{
    public function get(string $id): object;
}
