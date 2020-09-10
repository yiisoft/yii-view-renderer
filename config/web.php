<?php

declare(strict_types=1);

use Yiisoft\Yii\View\Csrf\CsrfToken;
use Yiisoft\Yii\View\Csrf\CsrfTokenInterface;

/**
 * @var array $params
 */

return [
    CsrfTokenInterface::class => CsrfToken::class,
];
