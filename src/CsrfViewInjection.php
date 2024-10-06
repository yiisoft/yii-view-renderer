<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer;

use LogicException;
use Yiisoft\Csrf\CsrfTokenInterface;
use Yiisoft\Csrf\CsrfTrait;

/**
 * `CsrfViewInjection` injects the necessary data into the view to protect against a CSRF attack.
 */
final class CsrfViewInjection implements CsrfParametersInjectionInterface
{
    use CsrfTrait;

    public const DEFAULT_META_ATTRIBUTE_NAME = 'csrf';
    public const DEFAULT_PARAMETER_NAME = 'csrf';
    public const META_TAG_KEY = 'csrf';

    private string $metaAttributeName = self::DEFAULT_META_ATTRIBUTE_NAME;
    private string $parameterName = self::DEFAULT_PARAMETER_NAME;

    public function __construct(private CsrfTokenInterface $token)
    {
    }


    /**
     * @throws LogicException when CSRF token is not defined
     */
    public function getCsrfParameters(): array
    {
        $tokenValue = $this->token->getValue();
        $csrf = new Csrf(
            $tokenValue,
            $this->getFormParameterName(),
            $this->getHeaderName(),
        );
        return [
            [
                $this->parameterName => $csrf
            ],
            [
                self::META_TAG_KEY => [
                    'name' => $this->metaAttributeName,
                    'content' => $tokenValue
                ],
            ],
        ];
    }


}
