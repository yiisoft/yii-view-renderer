<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\View\CsrfViewInjection;
use Yiisoft\Yii\View\Tests\Support\FakeCsrfToken;

final class CsrfViewInjectionTest extends TestCase
{
    public function testGetCommonParameters(): void
    {
        $token = '123';

        $parameters = $this->getInjection($token)->getCommonParameters();

        $this->assertCount(1, $parameters);
        $this->assertSame('csrf', key($parameters));
        $this->assertSame($token, current($parameters));
    }

    public function testGetLayoutParameters(): void
    {
        $token = '123';

        $parameters = $this->getInjection($token)->getLayoutParameters();

        $this->assertCount(1, $parameters);
        $this->assertSame('csrf', key($parameters));
        $this->assertSame($token, current($parameters));
    }

    public function testGetMetaTags(): void
    {
        $metaTags = $this->getInjection('123')->getMetaTags();

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
        $injection = $this->getInjection('123')->withParameterName('kitty');

        $commonParameters = $injection->getCommonParameters();
        $layoutParameters = $injection->getLayoutParameters();

        $this->assertSame('kitty', key($commonParameters));
        $this->assertSame('kitty', key($layoutParameters));
    }

    public function testWithMetaAttributeName(): void
    {
        $metaTags = $this->getInjection('123')
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

    private function getInjection(string $token = null): CsrfViewInjection
    {
        return new CsrfViewInjection(new FakeCsrfToken($token));
    }
}
