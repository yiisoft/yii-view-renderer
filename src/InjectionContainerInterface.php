<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

interface InjectionContainerInterface
{
    public function get(string $id): object;
}
