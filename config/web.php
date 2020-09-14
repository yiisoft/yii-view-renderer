<?php

declare(strict_types=1);

/**
 * @var array $params
 */

use Yiisoft\Yii\View\ViewRenderer;

return [
    ViewRenderer::class => [
        '__construct()' => [
            'viewBasePath' => $params['yiisoft/yii-view']['viewBasePath'],
            'layout' => $params['yiisoft/yii-view']['layout'],
            'injections' => [
                // Use for add Csrf parameter to all views
                // Reference::to(CsrfViewInjection::class),
                // or
                // DynamicReference::to(function (ContainerInterface $container) {
                //     return $container->get(CsrfViewInjection::class)->withParameter('mycsrf');
                // }),
            ],
        ],
    ],
];
