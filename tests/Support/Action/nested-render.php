<?php

declare(strict_types=1);

use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * @var WebViewRenderer $renderer
 */

echo 'Outer view. ' . $renderer->renderAsString('nested-template', ['name' => 'nested view']);
