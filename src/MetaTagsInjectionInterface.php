<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

/**
 * @psalm-type MetaTagsConfig = array<int, \Yiisoft\Html\Tag\Meta|array{
 *   __key?:string,
 *   0:\Yiisoft\Html\Tag\Meta
 * }>
 */
interface MetaTagsInjectionInterface
{
    /**
     * Returns array of {@see \Yiisoft\Html\Tag\Meta} tags for register via
     * {@see \Yiisoft\View\WebView::registerMetaTag()}.
     * Optionally, you may use array format and set the key that identifies the meta tag via `__key`.
     *
     * For example:
     *
     * ```php
     * [
     *     Html::meta()->name('keywords')->content('yii,framework'),
     *     [
     *         '__key' => 'description',
     *         Html::meta([
     *             'name' => 'description',
     *             'content' => 'This website is about funny raccoons.',
     *         ]),
     *     ],
     *     ...
     * ]
     * ```
     *
     * @return array
     *
     * @psalm-return MetaTagsConfig
     */
    public function getMetaTags(): array;
}
