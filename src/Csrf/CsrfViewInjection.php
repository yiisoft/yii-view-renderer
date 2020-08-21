<?php

namespace Yiisoft\Yii\View\Csrf;

use Yiisoft\Router\UrlMatcherInterface;
use Yiisoft\Yii\View\ContentParamsInjectionInterface;
use Yiisoft\Yii\View\LayoutParamsInjectionInterface;
use Yiisoft\Yii\View\MetaTagsInjectionInterface;

class CsrfViewInjection implements
    ContentParamsInjectionInterface,
    LayoutParamsInjectionInterface,
    MetaTagsInjectionInterface,
    CsrfViewInjectionInterface
{
    public const DEFAULT_REQUEST_ATTRIBUTE = 'csrf_token';
    public const DEFAULT_META_ATTRIBUTE = 'csrf';
    public const DEFAULT_PARAMETER = 'csrf';

    private UrlMatcherInterface $urlMatcher;

    private string $requestAttribute = self::DEFAULT_REQUEST_ATTRIBUTE;
    private string $metaAttribute = self::DEFAULT_META_ATTRIBUTE;
    private string $parameter = self::DEFAULT_PARAMETER;

    private ?string $csrfToken = null;

    public function __construct(UrlMatcherInterface $urlMatcher)
    {
        $this->urlMatcher = $urlMatcher;
    }

    public function withRequestAttribute(?string $requestAttribute = null): CsrfViewInjectionInterface
    {
        $clone = clone $this;
        $clone->requestAttribute = $requestAttribute ?? self::DEFAULT_REQUEST_ATTRIBUTE;
        return $clone;
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
        return [$this->parameter => $this->getCsrfToken()];
    }

    public function getLayoutParameters(): array
    {
        return [$this->parameter => $this->getCsrfToken()];
    }

    public function getMetaTags(): array
    {
        return [
            [
                '__key' => 'csrf_meta_tags',
                'name' => $this->metaAttribute,
                'content' => $this->getCsrfToken(),
            ]
        ];
    }

    private function getCsrfToken(): string
    {
        if ($this->csrfToken === null) {
            $this->csrfToken = $this->urlMatcher->getLastMatchedRequest()->getAttribute($this->requestAttribute);
        }
        return $this->csrfToken;
    }
}
