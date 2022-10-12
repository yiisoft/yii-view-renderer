<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

use Stringable;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Input;

final class Csrf implements Stringable
{
    public function __construct(private string $token, private string $parameterName, private string $headerName)
    {
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
