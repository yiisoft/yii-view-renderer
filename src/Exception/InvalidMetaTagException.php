<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Exception;

use RuntimeException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

/**
 * InvalidMetaTagException is thrown if the meta tag is incorrectly configured during the injection.
 */
final class InvalidMetaTagException extends RuntimeException implements FriendlyExceptionInterface
{
    /**
     * @var mixed
     */
    private $tag;

    /**
     * @param mixed $tag
     */
    public function __construct(string $message, $tag)
    {
        $this->tag = $tag;

        parent::__construct($message);
    }

    public function getName(): string
    {
        return 'Invalid meta tag configuration in injection';
    }

    public function getSolution(): ?string
    {
        return 'Got meta tag:' . "\n" . var_export($this->tag, true) . <<<SOLUTION


In injection that implements `Yiisoft\Yii\View\MetaTagsInjectionInterface` defined meta tags in the method `getMetaTags()`.

The meta tag can be define in the following ways:

- as array of attributes: `['name' => 'keywords', 'content' => 'yii,framework']`,
- as instance of `Yiisoft\Html\Tag\Meta`: 

```php
Html::meta()
    ->name('keywords')
    ->content('yii,framework');
```
Optionally, you may use string keys of array as identifies the meta tag.

Example:

```php
public function getMetaTags(): array
{
    return [
        'seo-keywords' => [
            'name' => 'keywords',
            'content' => 'yii,framework',
        ],
        Html::meta()
            ->name('description')
            ->content('Yii is a fast, secure, and efficient PHP framework.'),
    ];
}
```
SOLUTION;
    }
}
