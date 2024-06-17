<?php

declare(strict_types=1);

use Yiisoft\Yii\View\Renderer\InjectionContainer\InjectionContainer;
use Yiisoft\Yii\View\Renderer\InjectionContainer\InjectionContainerInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

/** @var array $params */

return [
    InjectionContainerInterface::class => InjectionContainer::class,
    ViewRenderer::class => [
        '__construct()' => [
            'viewPath' => $params['yiisoft/yii-view-renderer']['viewPath'],
            'layout' => $params['yiisoft/yii-view-renderer']['layout'],
            'injections' => $params['yiisoft/yii-view-renderer']['injections'],
        ],
    ],
];
