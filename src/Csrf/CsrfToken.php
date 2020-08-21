<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Csrf;

use Yiisoft\Router\UrlMatcherInterface;

final class CsrfToken implements CsrfTokenInterface
{

    private UrlMatcherInterface $urlMatcher;

    private string $requestAttributeName;

    private ?string $token = null;

    public function __construct(
        UrlMatcherInterface $urlMatcher,
        string $requestAttributeName = 'csrf_token'
    ) {
        $this->urlMatcher = $urlMatcher;
        $this->requestAttributeName = $requestAttributeName;
    }

    public function getToken(): string
    {
        if ($this->token === null) {
            $this->token = $this->urlMatcher->getLastMatchedRequest()->getAttribute($this->requestAttributeName);
        }
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}
