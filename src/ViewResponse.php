<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * A PSR-7 response decorator that supports deferred body rendering via a callable.
 *
 * @internal
 */
final class ViewResponse implements ResponseInterface
{
    private ?StreamInterface $resolvedBody = null;

    /**
     * @param ResponseInterface $response The wrapped PSR-7 response.
     * @param Closure $dataCallback The callable that produces the response body string.
     *
     * @psalm-param Closure(): StreamInterface $dataCallback
     */
    public function __construct(
        private ResponseInterface $response,
        private readonly Closure $dataCallback,
    ) {}

    public function getBody(): StreamInterface
    {
        if ($this->resolvedBody === null) {
            $this->resolvedBody = ($this->dataCallback)();
        }
        return $this->resolvedBody;
    }

    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): self
    {
        $new = clone $this;
        $new->response = $this->response->withProtocolVersion($version);
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): self
    {
        $new = clone $this;
        $new->response = $this->response->withHeader($name, $value);
        return $new;
    }

    public function withAddedHeader(string $name, $value): self
    {
        $new = clone $this;
        $new->response = $this->response->withAddedHeader($name, $value);
        return $new;
    }

    public function withoutHeader(string $name): self
    {
        $new = clone $this;
        $new->response = $this->response->withoutHeader($name);
        return $new;
    }

    public function withBody(StreamInterface $body): self
    {
        $new = clone $this;
        $new->response = $this->response->withBody($body);
        $new->resolvedBody = $body;
        return $new;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    public function withStatus(int $code, string $reasonPhrase = ''): self
    {
        $new = clone $this;
        $new->response = $this->response->withStatus($code, $reasonPhrase);
        return $new;
    }
}
