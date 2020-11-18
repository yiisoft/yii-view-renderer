<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests;

use LogicException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Csrf\CsrfToken;
use Yiisoft\Security\TokenMask;
use Yiisoft\Yii\View\CsrfViewInjection;
use Yiisoft\Yii\View\Tests\Support\FakeCsrfTokenStorage;

final class CsrfViewInjectionTest extends TestCase
{
    public function testGetContentPatameters(): void
    {
        $token = '123';

        $parameters = $this->getInjection($token)->getContentParameters();

        $this->assertCount(1, $parameters);
        $this->assertSame('csrf', key($parameters));
        $this->assertSame($token, TokenMask::remove(current($parameters)));
    }

    public function testGetContentParametersWhenNotDefinedCsrfToken(): void
    {
        $this->expectException(LogicException::class);
        $this->getInjection()->getContentParameters();
    }

    public function testGetLayoutPatameters(): void
    {
        $token = '123';

        $parameters = $this->getInjection($token)->getLayoutParameters();

        $this->assertCount(1, $parameters);
        $this->assertSame('csrf', key($parameters));
        $this->assertSame($token, TokenMask::remove(current($parameters)));
    }

    public function testGetLayoutParametersWhenNotDefinedCsrfToken(): void
    {
        $this->expectException(LogicException::class);
        $this->getInjection()->getLayoutParameters();
    }

    public function testGetMetaTags(): void
    {
        $token = '123';

        $metaTags = $this->getInjection($token)->getMetaTags();

        $this->assertCount(1, $metaTags);

        $metaTag = reset($metaTags);

        $this->assertArrayHasKey('content', $metaTag);

        $metaTag['content'] = TokenMask::remove($metaTag['content']);

        $this->assertSame(
            ['__key' => 'csrf_meta_tags', 'name' => 'csrf', 'content' => $token],
            $metaTag
        );
    }

    public function testGetMetaTagsWhenNotDefinedCsrfToken(): void
    {
        $this->expectException(LogicException::class);
        $this->getInjection()->getMetaTags();
    }

    public function testWithParameterName(): void
    {
        $injection = $this->getInjection('123')->withParameterName('kitty');

        $contentParameters = $injection->getContentParameters();
        $layoutParameters = $injection->getLayoutParameters();

        $this->assertSame('kitty', key($contentParameters));
        $this->assertSame('kitty', key($layoutParameters));
    }

    public function testWithMetaAttributeName(): void
    {
        $metaTags = $this->getInjection('123')
            ->withMetaAttributeName('kitty')
            ->getMetaTags();

        $this->assertSame('kitty', $metaTags[0]['name']);
    }

    public function testImmutability(): void
    {
        $original = $this->getInjection();
        $this->assertNotSame($original, $original->withMetaAttributeName('kitty'));
        $this->assertNotSame($original, $original->withParameterName('kitty'));
    }

    private function getInjection(string $token = null): CsrfViewInjection
    {
        $csrfTokenStorage = new FakeCsrfTokenStorage();
        if ($token !== null) {
            $csrfTokenStorage->set($token);
        }

        return new CsrfViewInjection(
            new CsrfToken($csrfTokenStorage)
        );
    }
}
