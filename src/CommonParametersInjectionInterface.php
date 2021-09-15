<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

/**
 * `CommonParametersInjectionInterface` is an interface that must be implemented by classes to inject
 * parameters to content and layout.
 */
interface CommonParametersInjectionInterface
{
    /**
     * Returns parameters for added to content and layout.
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
     * @return array
     *
     * @psalm-return array<string, mixed>
     */
    public function getCommonParameters(): array;
}
