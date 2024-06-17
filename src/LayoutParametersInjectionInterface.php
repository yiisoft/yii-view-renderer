<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer;

/**
 * LayoutParametersInjectionInterface is an interface that must be implemented by classes to inject layout parameters.
 */
interface LayoutParametersInjectionInterface
{
    /**
     * Returns parameters for added to layout.
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
    public function getLayoutParameters(): array;
}
