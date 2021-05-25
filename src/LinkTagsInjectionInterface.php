<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

/**
 * @psalm-type LinkTagsConfig = array<int, \Yiisoft\Html\Tag\Link|array{
 *   __key?:string,
 *   __position:int,
 *   0:\Yiisoft\Html\Tag\Link
 * }>
 */
interface LinkTagsInjectionInterface
{
    /**
     * Returns array of {@see \Yiisoft\Html\Tag\Link} tags for register via
     * {@see \Yiisoft\View\WebView::registerLinkTag()}.
     * Optionally, you may use array format and set the key that identifies the link tag via `__key` and the position
     * in a page via `__position`.
     *
     * For example:
     *
     * ```php
     * [
     *     Html::link()->toCssFile('/main.css'),
     *     [
     *         '__key' => 'favicon',
     *         Html::link('/myicon.png', [
     *             'rel' => 'icon',
     *             'type' => 'image/png',
     *         ]),
     *     ],
     *     [
     *         '__key' => 'themeCss',
     *         '__position' => \Yiisoft\View\WebView::POSITION_END,
     *         Html::link()->toCssFile('/theme.css'),
     *     ],
     *     ...
     * ]
     * ```
     *
     * @return array
     *
     * @psalm-return LinkTagsConfig
     */
    public function getLinkTags(): array;
}
