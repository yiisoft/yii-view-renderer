<?php

declare(strict_types=1);

use Yiisoft\Yii\View\Renderer\Debug\WebViewCollector;

return [
    'yiisoft/yii-view-renderer' => [
        'viewPath' => '@views',
        'layout' => '@layout/main',
        'injections' => [
            // Use for add Csrf parameter to all views
            // Reference::to(CsrfViewInjection::class),
            // or
            // DynamicReference::to(function (ContainerInterface $container) {
            //     return $container
            //         ->get(CsrfViewInjection::class)
            //         ->withParameter('mycsrf');
            // }),
        ],
    ],
    'yiisoft/yii-debug' => [
        'collectors.web' => [
            WebViewCollector::class,
        ],
    ],
];
