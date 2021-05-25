<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Exception;

use RuntimeException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;
use Yiisoft\Html\Tag\Meta;

use function get_class;
use function gettype;
use function is_object;

final class InvalidMetaTagException extends RuntimeException implements FriendlyExceptionInterface
{
    /**
     * @var mixed
     */
    private $tag;

    /**
     * @param mixed $tag
     */
    public function __construct($tag)
    {
        $this->tag = $tag;

        parent::__construct(
            sprintf(
                'Meta tag in injection should be instance of %s or an array. Got %s.',
                Meta::class,
                is_object($tag) ? get_class($tag) : gettype($tag)
            )
        );
    }

    public function getName(): string
    {
        return 'Invalid meta tag configuration in injection';

    }

    public function getSolution(): ?string
    {
        return 'Got meta tag:' . "\n" . var_export($this->tag, true);
    }
}
