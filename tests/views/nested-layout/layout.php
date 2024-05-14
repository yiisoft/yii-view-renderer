<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var string $content
 * @var string $title
 */

$this->beginPage();
echo '<html>';
echo $this->render('head');
echo '<body>';
echo '<h1>' . $title . '</h1>';
echo $content;
echo '</body>';
echo '</html>';
$this->endPage();
