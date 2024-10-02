<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer;

use Stringable;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Input;

final class Csrf implements Stringable
{
    public function __construct(
        private readonly string $token,
        private readonly string $parameterName,
        private readonly string $headerName,
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    public function getHeaderName(): string
    {
        return $this->headerName;
    }

    public function hiddenInput(array $attributes = []): Input
    {
        $tag = Html::hiddenInput($this->parameterName, $this->token);
        return $attributes === [] ? $tag : $tag->addAttributes($attributes);
    }

    public function __toString(): string
    {
        return $this->getToken();
    }
}
