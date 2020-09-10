<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Csrf;

use LogicException;
use Yiisoft\Yii\View\ContentParametersInjectionInterface;
use Yiisoft\Yii\View\LayoutParametersInjectionInterface;
use Yiisoft\Yii\View\MetaTagsInjectionInterface;

final class CsrfViewInjection implements
    ContentParametersInjectionInterface,
    LayoutParametersInjectionInterface,
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
        return [$this->parameterName => $this->getCsrfToken()];
    }

    public function getLayoutParameters(): array
    {
        return [$this->parameterName => $this->getCsrfToken()];
    }

    public function getMetaTags(): array
    {
        return [
            [
                '__key' => 'csrf_meta_tags',
                'name' => $this->metaAttributeName,
                'content' => $this->getCsrfToken(),
            ]
        ];
    }

    /**
     * @return string
     * @throws LogicException
     */
    private function getCsrfToken(): string
    {
        $token = $this->csrfToken->get();
        if (empty($token)) {
            throw new LogicException('CSRF token is not defined.');
        }
        return $token;
    }
}
