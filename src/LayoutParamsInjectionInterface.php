<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

interface LayoutParamsInjectionInterface
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
     * @return array
     */
    public function getLayoutParameters(): array;
}
