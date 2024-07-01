<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var string $name
 */

echo($label ?? 'nested-1') . ': ' . $name . '. ' . $this->render('./nested-2');
