<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

/**
 * LinkTagsInjectionInterface is an interface that must be implemented by classes to inject link tags.
 *
 * @psalm-type LinkTagAsArray = array{
 *   __position?:int,
 * }&array<string,mixed>
 * @psalm-type LinkTagsConfig = array<array-key, \Yiisoft\Html\Tag\Link|LinkTagAsArray|array{
 *   __position?:int,
 *   0:\Yiisoft\Html\Tag\Link
 * }>
 */
interface LinkTagsInjectionInterface
{
    /**
     * Returns array of link tags for register via {@see \Yiisoft\View\WebView::registerLinkTag()}.
     * Optionally:
     *  - use array format and set the position in a page via `__position`.
     *  - use string keys of array as identifies the link tag.
     *
     * For example:
     *
     * ```php
     * [
     *     Html::link()->toCssFile('/main.css'),
     *     'favicon' => Html::link('/myicon.png', [
     *         'rel' => 'icon',
     *         'type' => 'image/png',
     *     ]),
     *     'themeCss' => [
     *         '__position' => \Yiisoft\View\WebView::POSITION_END,
     *         Html::link()->toCssFile('/theme.css'),
     *     ],
     *     'userCss' => [
     *         '__position' => \Yiisoft\View\WebView::POSITION_BEGIN,
     *         'rel' => 'stylesheet',
     *         'href' => '/user.css',
     *     ],
     *     ...
     * ]
     * ```
     *
     * @psalm-return LinkTagsConfig
     */
    public function getLinkTags(): array;
}
