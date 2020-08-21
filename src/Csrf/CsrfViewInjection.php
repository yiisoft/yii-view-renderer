<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Csrf;

use Yiisoft\Yii\View\ContentParamsInjectionInterface;
use Yiisoft\Yii\View\LayoutParamsInjectionInterface;
use Yiisoft\Yii\View\MetaTagsInjectionInterface;

final class CsrfViewInjection implements
    ContentParamsInjectionInterface,
    LayoutParamsInjectionInterface,
    MetaTagsInjectionInterface
{
    public const DEFAULT_META_ATTRIBUTE_NAME = 'csrf';
    public const DEFAULT_PARAMETER_NAME = 'csrf';

    private string $metaAttributeName = self::DEFAULT_META_ATTRIBUTE_NAME;
    private string $parameterName = self::DEFAULT_PARAMETER_NAME;

    private CsrfTokenInterface $csrfToken;

    public function __construct(CsrfTokenInterface $csrfToken)
    {
        $this->csrfToken = $csrfToken;
    }

    public function withParameterName(string $parameterName): self
    {
        $clone = clone $this;
        $clone->parameterName = $parameterName;
        return $clone;
    }

    public function withMetaAttributeName(string $metaAttributeName): self
    {
        $clone = clone $this;
        $clone->metaAttributeName = $metaAttributeName;
        return $clone;
    }

    public function getContentParameters(): array
    {
        return [$this->parameterName => $this->csrfToken->getToken()];
    }

    public function getLayoutParameters(): array
    {
        return [$this->parameterName => $this->csrfToken->getToken()];
    }

    public function getMetaTags(): array
    {
        return [
            [
                '__key' => 'csrf_meta_tags',
                'name' => $this->metaAttributeName,
                'content' => $this->csrfToken->getToken(),
            ]
        ];
    }
}
