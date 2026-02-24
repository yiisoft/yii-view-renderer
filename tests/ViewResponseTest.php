<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer\Tests;

use HttpSoft\Message\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Yiisoft\Test\Support\HttpMessage\StringStream;
use Yiisoft\Yii\View\Renderer\ViewResponse;

final class ViewResponseTest extends TestCase
{
    public function testGetBodyReturnsCallbackResult(): void
    {
        $stream = new StringStream('hello world');

        $response = new ViewResponse(
            new Response(),
            fn(): StreamInterface => $stream,
        );

        $this->assertSame('hello world', (string) $response->getBody());
    }

    public function testGetBodyCallsCallbackOnce(): void
    {
        $callCount = 0;
        $stream = new StringStream('test content');

        $response = new ViewResponse(
            new Response(),
            function () use (&$callCount, $stream): StreamInterface {
                $callCount++;
                return $stream;
            },
        );

        $body1 = $response->getBody();
        $body2 = $response->getBody();

        $this->assertSame(1, $callCount);
        $this->assertSame($stream, $body1);
        $this->assertSame($body1, $body2);
    }

    public function testGetBodyIsLazy(): void
    {
        $called = false;

        $response = new ViewResponse(
            new Response(),
            function () use (&$called): StreamInterface {
                $called = true;
                return new StringStream('lazy');
            },
        );

        $this->assertFalse($called);

        $response->getBody();

        $this->assertTrue($called);
    }

    public function testGetProtocolVersion(): void
    {
        $response = new ViewResponse(
            (new Response())->withProtocolVersion('2.0'),
            fn(): StreamInterface => new StringStream(),
        );

        $this->assertSame('2.0', $response->getProtocolVersion());
    }

    public function testWithProtocolVersion(): void
    {
        $response = new ViewResponse(
            new Response(),
            fn(): StreamInterface => new StringStream(),
        );

        $newResponse = $response->withProtocolVersion('2.0');

        $this->assertNotSame($response, $newResponse);
        $this->assertSame('2.0', $newResponse->getProtocolVersion());
        $this->assertSame('1.1', $response->getProtocolVersion());
    }

    public function testGetHeaders(): void
    {
        $response = new ViewResponse(
            (new Response())
                ->withHeader('X-Test', 'value1')
                ->withHeader('X-Another', 'value2'),
            fn(): StreamInterface => new StringStream(),
        );

        $headers = $response->getHeaders();

        $this->assertSame(['value1'], $headers['X-Test']);
        $this->assertSame(['value2'], $headers['X-Another']);
    }

    public function testHasHeader(): void
    {
        $response = new ViewResponse(
            (new Response())->withHeader('X-Test', 'value'),
            fn(): StreamInterface => new StringStream(),
        );

        $this->assertTrue($response->hasHeader('X-Test'));
        $this->assertFalse($response->hasHeader('X-Missing'));
    }

    public function testGetHeader(): void
    {
        $response = new ViewResponse(
            (new Response())->withHeader('X-Test', 'value'),
            fn(): StreamInterface => new StringStream(),
        );

        $this->assertSame(['value'], $response->getHeader('X-Test'));
        $this->assertSame([], $response->getHeader('X-Missing'));
    }

    public function testGetHeaderLine(): void
    {
        $response = new ViewResponse(
            (new Response())
                ->withHeader('X-Test', 'value1')
                ->withAddedHeader('X-Test', 'value2'),
            fn(): StreamInterface => new StringStream(),
        );

        $this->assertSame('value1,value2', $response->getHeaderLine('X-Test'));
        $this->assertSame('', $response->getHeaderLine('X-Missing'));
    }

    public function testWithHeader(): void
    {
        $response = new ViewResponse(
            new Response(),
            fn(): StreamInterface => new StringStream(),
        );

        $newResponse = $response->withHeader('X-Test', 'value');

        $this->assertNotSame($response, $newResponse);
        $this->assertTrue($newResponse->hasHeader('X-Test'));
        $this->assertFalse($response->hasHeader('X-Test'));
    }

    public function testWithAddedHeader(): void
    {
        $response = (new ViewResponse(
            new Response(),
            fn(): StreamInterface => new StringStream(),
        ))->withHeader('X-Test', 'value1');

        $newResponse = $response->withAddedHeader('X-Test', 'value2');

        $this->assertNotSame($response, $newResponse);
        $this->assertSame('value1,value2', $newResponse->getHeaderLine('X-Test'));
        $this->assertSame('value1', $response->getHeaderLine('X-Test'));
    }

    public function testWithoutHeader(): void
    {
        $response = (new ViewResponse(
            new Response(),
            fn(): StreamInterface => new StringStream(),
        ))->withHeader('X-Test', 'value');

        $newResponse = $response->withoutHeader('X-Test');

        $this->assertNotSame($response, $newResponse);
        $this->assertFalse($newResponse->hasHeader('X-Test'));
        $this->assertTrue($response->hasHeader('X-Test'));
    }

    public function testWithBody(): void
    {
        $response = new ViewResponse(
            new Response(),
            fn(): StreamInterface => new StringStream(),
        );

        $newResponse = $response->withBody(new StringStream('new body'));

        $this->assertNotSame($response, $newResponse);
        $this->assertSame('new body', (string) $newResponse->getBody());
    }

    public function testWithBodyOverridesCallback(): void
    {
        $callbackCalled = false;
        $response = new ViewResponse(
            new Response(),
            function () use (&$callbackCalled): StreamInterface {
                $callbackCalled = true;
                return new StringStream('from callback');
            },
        );

        $newResponse = $response->withBody(new StringStream('overridden'));

        $this->assertSame('overridden', (string) $newResponse->getBody());
        $this->assertFalse($callbackCalled);
    }

    public function testGetStatusCode(): void
    {
        $response = new ViewResponse(
            (new Response())->withStatus(404),
            fn(): StreamInterface => new StringStream(),
        );

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testGetReasonPhrase(): void
    {
        $response = new ViewResponse(
            (new Response())->withStatus(404, 'Custom Not Found'),
            fn(): StreamInterface => new StringStream(),
        );

        $this->assertSame('Custom Not Found', $response->getReasonPhrase());
    }

    public function testWithStatus(): void
    {
        $response = new ViewResponse(
            new Response(),
            fn(): StreamInterface => new StringStream(),
        );

        $newResponse = $response->withStatus(500, 'Internal Error');

        $this->assertNotSame($response, $newResponse);
        $this->assertSame(500, $newResponse->getStatusCode());
        $this->assertSame('Internal Error', $newResponse->getReasonPhrase());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testImmutabilityPreservesCallback(): void
    {
        $response = new ViewResponse(
            new Response(),
            fn(): StreamInterface => new StringStream('original'),
        );

        $cloned = $response->withStatus(201);

        $this->assertSame('original', (string) $cloned->getBody());
    }

    public function testDefaultStatusCode(): void
    {
        $response = new ViewResponse(
            new Response(),
            fn(): StreamInterface => new StringStream(),
        );

        $this->assertSame(200, $response->getStatusCode());
    }
}
