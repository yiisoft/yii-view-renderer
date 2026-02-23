<?php

declare(strict_types=1);
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var string $name
 * @var string $label
 */

echo $label . ': ' . $name . '. ' . $this->render('./nested-1');
