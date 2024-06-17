<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer;

/**
 * MetaTagsInjectionInterface is an interface that must be implemented by classes to inject meta tags.
 *
 * @psalm-type MetaTagsConfig = array<array-key, \Yiisoft\Html\Tag\Meta|array<string,mixed>>
 */
interface MetaTagsInjectionInterface
{
    /**
     * Returns array of meta tags for register via {@see \Yiisoft\View\WebView::registerMetaTag()}.
     * Optionally, you may use string keys of array as identifies the meta tag.
     *
     * For example:
     *
     * ```php
     * [
     *     Html::meta()
     *         ->name('http-equiv')
     *         ->content('public'),
     *     'noindex' => Html::meta()
     *         ->name('robots')
     *         ->content('noindex'),
     *     [
     *         'name' => 'description',
     *         'content' => 'This website is about funny raccoons.',
     *     ],
     *     'keywords' => [
     *         'name' => 'keywords',
     *         'content' => 'yii,framework',
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
