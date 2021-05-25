<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Exception;

use RuntimeException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

use function get_class;
use function gettype;
use function is_object;

final class InvalidLinkTagKeyException extends RuntimeException implements FriendlyExceptionInterface
{
    private array $tag;

    /**
     * @param mixed $key
     */
    public function __construct($key, array $tag)
    {
        $this->tag = $tag;

        parent::__construct(
            sprintf(
                'Link tag key in injection should be string or null. Got %s.',
                is_object($key) ? get_class($key) : gettype($key)
            )
        );
    }

    public function getName(): string
    {
        return 'Invalid link tag key in injection';
    }

    public function getSolution(): ?string
    {
        return 'Got link tag configuration:' . "\n" . var_export($this->tag, true);
    }
}
