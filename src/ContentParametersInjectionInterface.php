<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

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
     */
    public function getContentParameters(): array;
}
