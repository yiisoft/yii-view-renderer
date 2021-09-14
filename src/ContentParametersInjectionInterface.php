<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

/**
 * ContentParametersInjectionInterface is an interface that must be implemented by classes to inject content parameters.
 */
interface ContentParametersInjectionInterface
{
    /**
     * Returns parameters for added to content.
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
    public function getContentParameters(): array;
}
