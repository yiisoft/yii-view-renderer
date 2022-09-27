<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

/**
 * `CommonParametersInjectionInterface` is an interface that must be implemented by classes to inject
 * parameters both to view template and to layout.
 */
interface CommonParametersInjectionInterface
{
    /**
     * Returns parameters for added both to view template and to layout.
     *
     * For example:
     *
     * ```
     * [
     *     'paramA' => 'something',
     *     'paramB' => 42,
     *     ...
     * ]
     * ```
     *
     * @psalm-return array<string, mixed>
     */
    public function getCommonParameters(): array;
}
