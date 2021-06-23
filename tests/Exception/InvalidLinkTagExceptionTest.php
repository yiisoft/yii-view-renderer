<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Tests\Exception;

use Yiisoft\Yii\View\Exception\InvalidLinkTagException;
use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\View\Tests\Support\TestTrait;

final class InvalidLinkTagExceptionTest extends TestCase
{
    use TestTrait;

    public function testBase(): void
    {
        $exception = new InvalidLinkTagException('test', []);

        $this->assertSame('Invalid link tag configuration in injection', $exception->getName());
        $this->assertStringStartsWithIgnoringLineEndings(
            "Got link tag:\narray (\n)\n\nIn injection that implements",
            $exception->getSolution(),
        );
    }
}
