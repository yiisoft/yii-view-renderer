<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

use LogicException;
use Yiisoft\Csrf\CsrfToken;

final class CsrfViewInjection implements
    ContentParametersInjectionInterface,
    LayoutParametersInjectionInterface,
    MetaTagsInjectionInterface
{
    public const DEFAULT_META_ATTRIBUTE_NAME = 'csrf';
    public const DEFAULT_PARAMETER_NAME = 'csrf';

    private string $metaAttributeName = self::DEFAULT_META_ATTRIBUTE_NAME;
    private string $parameterName = self::DEFAULT_PARAMETER_NAME;

    private CsrfToken $csrfToken;

    public function __construct(CsrfToken $csrfToken)
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

    /**
     * @throws LogicException when CSRF token is not defined
     */
    public function getContentParameters(): array
    {
        return [$this->parameterName => $this->csrfToken->getValue()];
    }

    /**
     * @throws LogicException when CSRF token is not defined
     */
    public function getLayoutParameters(): array
    {
        return [$this->parameterName => $this->csrfToken->getValue()];
    }

    /**
     * @throws LogicException when CSRF token is not defined
     */
    public function getMetaTags(): array
    {
        return [
            [
                '__key' => 'csrf_meta_tags',
                'name' => $this->metaAttributeName,
                'content' => $this->csrfToken->getValue(),
            ]
        ];
    }
}
