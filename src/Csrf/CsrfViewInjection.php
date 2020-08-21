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
    public const DEFAULT_META_ATTRIBUTE = 'csrf';
    public const DEFAULT_PARAMETER = 'csrf';

    private string $metaAttribute = self::DEFAULT_META_ATTRIBUTE;
    private string $parameter = self::DEFAULT_PARAMETER;

    private CsrfTokenInterface $csrfToken;

    public function __construct(CsrfTokenInterface $csrfToken)
    {
        $this->csrfToken = $csrfToken;
    }

    public function withParameter(string $parameter): self
    {
        $clone = clone $this;
        $clone->parameter = $parameter;
        return $clone;
    }

    public function withMetaAttribute(string $metaAttribute): self
    {
        $clone = clone $this;
        $clone->metaAttribute = $metaAttribute;
        return $clone;
    }

    public function getContentParameters(): array
    {
        return [$this->parameter => $this->csrfToken->getToken()];
    }

    public function getLayoutParameters(): array
    {
        return [$this->parameter => $this->csrfToken->getToken()];
    }

    public function getMetaTags(): array
    {
        return [
            [
                '__key' => 'csrf_meta_tags',
                'name' => $this->metaAttribute,
                'content' => $this->csrfToken->getToken(),
            ]
        ];
    }
}
