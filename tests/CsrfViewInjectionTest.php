<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\CsrfMiddleware;
use Yiisoft\Csrf\Synchronizer\Generator\RandomCsrfTokenGenerator;
use Yiisoft\Csrf\Synchronizer\SynchronizerCsrfToken;
use Yiisoft\Yii\View\Renderer\Csrf;
use Yiisoft\Yii\View\Renderer\CsrfViewInjection;
use Yiisoft\Yii\View\Renderer\Tests\Support\FakeCsrfToken;
use Yiisoft\Yii\View\Renderer\Tests\Support\MockCsrfTokenStorage;

final class CsrfViewInjectionTest extends TestCase
{
    public function testGetCommonParameters(): void
    {
        $parameters = $this
            ->getInjection('123', 'p-csrf', 'h-csrf')
            ->getCommonParameters();

        $this->assertCount(1, $parameters);
        $this->assertSame('csrf', key($parameters));

        /** @var Csrf $csrf */
        $csrf = current($parameters);
        $this->assertInstanceOf(Csrf::class, $csrf);

        $this->assertSame('123', (string) $csrf);
        $this->assertSame('123', $csrf->getToken());
        $this->assertSame('p-csrf', $csrf->getParameterName());
        $this->assertSame('h-csrf', $csrf->getHeaderName());
        $this->assertSame(
            '<input type="hidden" name="p-csrf" value="123">',
            (string) $csrf->hiddenInput()
        );
        $this->assertSame(
            '<input type="hidden" name="p-csrf" value="123" data-key="42">',
            (string) $csrf->hiddenInput(['data-key' => 42])
        );
    }

    public function testGetMetaTags(): void
    {
        $metaTags = $this
            ->getInjection('123')
            ->getMetaTags();

        $this->assertSame(
            [
                CsrfViewInjection::META_TAG_KEY => [
                    'name' => CsrfViewInjection::DEFAULT_META_ATTRIBUTE_NAME,
                    'content' => '123',
                ],
            ],
            $metaTags
        );
    }

    public function testWithParameterName(): void
    {
        $injection = $this
            ->getInjection('123')
            ->withParameterName('kitty');

        $commonParameters = $injection->getCommonParameters();

        $this->assertSame('kitty', key($commonParameters));
    }

    public function testWithMetaAttributeName(): void
    {
        $metaTags = $this
            ->getInjection('123')
            ->withMetaAttributeName('kitty')
            ->getMetaTags();

        $this->assertSame(
            [
                CsrfViewInjection::META_TAG_KEY => [
                    'name' => 'kitty',
                    'content' => '123',
                ],
            ],
            $metaTags
        );
    }

    public function testImmutability(): void
    {
        $original = $this->getInjection();
        $this->assertNotSame($original, $original->withMetaAttributeName('kitty'));
        $this->assertNotSame($original, $original->withParameterName('kitty'));
    }

    private function getInjection(
        ?string $token = null,
        ?string $middlewareParameterName = null,
        ?string $middlewareHeaderName = null
    ): CsrfViewInjection {
        $token = new FakeCsrfToken($token);

        $middleware = new CsrfMiddleware(
            new Psr17Factory(),
            new SynchronizerCsrfToken(
                new RandomCsrfTokenGenerator(),
                new MockCsrfTokenStorage()
            )
        );
        if ($middlewareParameterName !== null) {
            $middleware = $middleware->withParameterName($middlewareParameterName);
        }
        if ($middlewareHeaderName !== null) {
            $middleware = $middleware->withHeaderName($middlewareHeaderName);
        }

        return new CsrfViewInjection($token, $middleware);
    }
}
