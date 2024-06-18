<?php

declare(strict_types=1);

use Yiisoft\Yii\View\Renderer\Debug\WebViewCollector;

return [
    'yiisoft/yii-view-renderer' => [
        // The full path to the directory of views or its alias.
        // If null, relative view paths in `ViewRenderer::render()` is not available.
        'viewPath' => null,

        // The full path to the layout file to be applied to views.
        // If null, the layout will not be applied.
        'layout' => null,

        // The injection instances or class names.
        'injections' => [],
    ],
    'yiisoft/yii-debug' => [
        'collectors.web' => [
            WebViewCollector::class,
        ],
    ],
];
