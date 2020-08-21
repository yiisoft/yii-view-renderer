<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

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
     * @return array
     */
    public function getLayoutParameters(): array;
}
