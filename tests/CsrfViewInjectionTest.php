<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Html\Tag\Meta;
use Yiisoft\Yii\View\CsrfViewInjection;
use Yiisoft\Yii\View\Tests\Support\FakeCsrfToken;

final class CsrfViewInjectionTest extends TestCase
{
    public function testGetContentPatameters(): void
    {
        $token = '123';

        $parameters = $this->getInjection($token)->getContentParameters();

        $this->assertCount(1, $parameters);
        $this->assertSame('csrf', key($parameters));
        $this->assertSame($token, current($parameters));
    }

    public function testGetLayoutPatameters(): void
    {
        $token = '123';

        $parameters = $this->getInjection($token)->getLayoutParameters();

        $this->assertCount(1, $parameters);
        $this->assertSame('csrf', key($parameters));
        $this->assertSame($token, current($parameters));
    }

    public function testGetMetaTags(): void
    {
        $token = '123';

        $metaTags = $this->getInjection($token)->getMetaTags();

        $this->assertCount(1, $metaTags);

        $metaTag = reset($metaTags);

        $this->assertArrayHasKey('__key', $metaTag);
        $this->assertArrayHasKey(0, $metaTag);
        $this->assertInstanceOf(Meta::class, $metaTag[0]);

        $this->assertSame('csrf_meta_tags', $metaTag['__key']);
        $this->assertSame('<meta name="csrf" content="123">', $metaTag[0]->render());
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

        $this->assertSame('<meta name="kitty" content="123">', $metaTags[0][0]->render());
    }

    public function testImmutability(): void
    {
        $original = $this->getInjection();
        $this->assertNotSame($original, $original->withMetaAttributeName('kitty'));
        $this->assertNotSame($original, $original->withParameterName('kitty'));
    }

    private function getInjection(string $token = null): CsrfViewInjection
    {
        return new CsrfViewInjection(
            new FakeCsrfToken($token)
        );
    }
}
