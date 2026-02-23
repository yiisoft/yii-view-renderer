<?php

declare(strict_types=1);
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var string $title
 */

echo '<head>';
echo '<title>' . $title . '</title>';
$this->head();
echo '</head>';
