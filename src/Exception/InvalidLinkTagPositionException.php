<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Exception;

use RuntimeException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

use function get_class;
use function gettype;
use function is_object;

final class InvalidLinkTagPositionException extends RuntimeException implements FriendlyExceptionInterface
{
    private array $tag;

    /**
     * @param mixed $position
     */
    public function __construct($position, array $tag)
    {
        $this->tag = $tag;

        parent::__construct(
            sprintf(
                'Link tag position in injection should be integer. Got %s.',
                is_object($position) ? get_class($position) : gettype($position)
            )
        );
    }

    public function getName(): string
    {
        return 'Invalid link tag position in injection ';
    }

    public function getSolution(): ?string
    {
        return 'Got link tag configuration:' . "\n" . var_export($this->tag, true);
    }
}
