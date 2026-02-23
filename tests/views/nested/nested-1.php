<?php

declare(strict_types=1);
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var string $name
 */

echo($label ?? 'nested-1') . ': ' . $name . '. ' . $this->render('./nested-2');
