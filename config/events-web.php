<?php

declare(strict_types=1);

use Yiisoft\View\Event\WebView\AfterRender;
use Yiisoft\Yii\View\Debug\WebViewCollector;

return [
    AfterRender::class => [
        [WebViewCollector::class, 'collect'],
    ],
];
