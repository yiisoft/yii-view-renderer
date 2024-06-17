<?php

declare(strict_types=1);

use Yiisoft\View\Event\WebView\AfterRender;
use Yiisoft\Yii\View\Renderer\Debug\WebViewCollector;

if (!($params['yiisoft/yii-debug']['enabled'] ?? false)) {
    return [];
}

return [
    AfterRender::class => [
        [WebViewCollector::class, 'collect'],
    ],
];
