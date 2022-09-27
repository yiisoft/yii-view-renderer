<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

use LogicException;
use Yiisoft\Csrf\CsrfMiddleware;
use Yiisoft\Csrf\CsrfTokenInterface;

/**
 * `CsrfViewInjection` injects the necessary data into the view to protect against a CSRF attack.
 */
final class CsrfViewInjection implements CommonParametersInjectionInterface, MetaTagsInjectionInterface
{
    public const DEFAULT_META_ATTRIBUTE_NAME = 'csrf';
    public const DEFAULT_PARAMETER_NAME = 'csrf';
    public const META_TAG_KEY = 'csrf';

    private string $metaAttributeName = self::DEFAULT_META_ATTRIBUTE_NAME;
    private string $parameterName = self::DEFAULT_PARAMETER_NAME;

    public function __construct(private CsrfTokenInterface $token, private CsrfMiddleware $middleware)
    {
    }

    /**
     * Returns a new instance with the specified parameter name.
     *
     * @param string $parameterName The parameter name.
     */
    public function withParameterName(string $parameterName): self
    {
        $new = clone $this;
        $new->parameterName = $parameterName;
        return $new;
    }

    /**
     * Returns a new instance with the specified meta attribute name.
     *
     * @param string $metaAttributeName The meta attribute name.
     */
    public function withMetaAttributeName(string $metaAttributeName): self
    {
        $new = clone $this;
        $new->metaAttributeName = $metaAttributeName;
        return $new;
    }

    /**
     * @throws LogicException when CSRF token is not defined
     */
    public function getCommonParameters(): array
    {
        $csrf = new Csrf(
            $this->token->getValue(),
            $this->middleware->getParameterName(),
            $this->middleware->getHeaderName(),
        );
        return [$this->parameterName => $csrf];
    }

    /**
     * @throws LogicException when CSRF token is not defined
     */
    public function getMetaTags(): array
    {
        return [
            self::META_TAG_KEY => [
                'name' => $this->metaAttributeName,
                'content' => $this->token->getValue(),
            ],
        ];
    }
}
