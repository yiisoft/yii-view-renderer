<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var string $h1
 */

$this->setParameter('seoTitle', 'TITLE / ' . $h1);

echo '<h1>' . $h1 . '</h1>';
