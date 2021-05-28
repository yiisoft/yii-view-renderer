<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

use LogicException;
use Yiisoft\Csrf\CsrfTokenInterface;

final class CsrfViewInjection implements
    ContentParametersInjectionInterface,
    LayoutParametersInjectionInterface,
    MetaTagsInjectionInterface
{
    public const DEFAULT_META_ATTRIBUTE_NAME = 'csrf';
    public const DEFAULT_PARAMETER_NAME = 'csrf';
    public const META_TAG_KEY = 'csrf';

    private string $metaAttributeName = self::DEFAULT_META_ATTRIBUTE_NAME;
    private string $parameterName = self::DEFAULT_PARAMETER_NAME;

    private CsrfTokenInterface $csrfToken;

    public function __construct(CsrfTokenInterface $csrfToken)
    {
        $this->csrfToken = $csrfToken;
    }

    public function withParameterName(string $parameterName): self
    {
        $new = clone $this;
        $new->parameterName = $parameterName;
        return $new;
    }

    public function withMetaAttributeName(string $metaAttributeName): self
    {
        $new = clone $this;
        $new->metaAttributeName = $metaAttributeName;
        return $new;
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
            self::META_TAG_KEY => [
                'name' => $this->metaAttributeName,
                'content' => $this->csrfToken->getValue(),
            ],
        ];
    }
}
