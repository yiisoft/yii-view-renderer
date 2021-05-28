<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Link;
use Yiisoft\Html\Tag\Meta;
use Yiisoft\Strings\Inflector;
use Yiisoft\View\ViewContextInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\Exception\InvalidLinkTagException;
use Yiisoft\Yii\View\Exception\InvalidLinkTagPositionException;
use Yiisoft\Yii\View\Exception\InvalidMetaTagException;

use function array_key_exists;
use function get_class;
use function is_array;
use function is_int;
use function is_string;

/**
 * @psalm-import-type MetaTagsConfig from MetaTagsInjectionInterface
 * @psalm-import-type LinkTagsConfig from LinkTagsInjectionInterface
 */
final class ViewRenderer implements ViewContextInterface
{
    private DataResponseFactoryInterface $responseFactory;
    private Aliases $aliases;
    private WebView $view;

    private string $viewBasePath;
    private ?string $layout;

    /**
     * @var object[]
     */
    private array $injections;

    private ?string $name = null;
    private ?string $viewPath = null;

    /**
     * @param DataResponseFactoryInterface $responseFactory
     * @param Aliases $aliases
     * @param WebView $view
     * @param string $viewBasePath
     * @param string|null $layout
     * @param object[] $injections
     */
    public function __construct(
        DataResponseFactoryInterface $responseFactory,
        Aliases $aliases,
        WebView $view,
        string $viewBasePath,
        ?string $layout = null,
        array $injections = []
    ) {
        $this->responseFactory = $responseFactory;
        $this->aliases = $aliases;
        $this->view = $view;

        $this->viewBasePath = $viewBasePath;
        $this->layout = $layout;
        $this->injections = $injections;
    }

    public function getViewPath(): string
    {
        if ($this->viewPath !== null) {
            return $this->aliases->get($this->viewPath);
        }

        return $this->aliases->get($this->viewBasePath) . ($this->name ? '/' . $this->name : '');
    }

    public function render(string $view, array $parameters = []): ResponseInterface
    {
        $contentParameters = $this->getContentParameters($parameters);
        $layoutParameters = $this->getLayoutParameters();
        $metaTags = $this->getMetaTags();
        $linkTags = $this->getLinkTags();

        $contentRenderer = fn (): string => $this->renderProxy(
            $view,
            $contentParameters,
            $layoutParameters,
            $metaTags,
            $linkTags
        );

        return $this->responseFactory->createResponse($contentRenderer);
    }

    private function getContentParameters(array $parameters): array
    {
        foreach ($this->injections as $injection) {
            if ($injection instanceof ContentParametersInjectionInterface) {
                $parameters = array_merge($parameters, $injection->getContentParameters());
            }
        }
        return $parameters;
    }

    private function getLayoutParameters(): array
    {
        $parameters = [];
        foreach ($this->injections as $injection) {
            if ($injection instanceof LayoutParametersInjectionInterface) {
                $parameters = array_merge($parameters, $injection->getLayoutParameters());
            }
        }
        return $parameters;
    }

    /**
     * @psalm-return MetaTagsConfig
     */
    private function getMetaTags(): array
    {
        $tags = [];
        foreach ($this->injections as $injection) {
            if ($injection instanceof MetaTagsInjectionInterface) {
                $tags = array_merge($tags, $injection->getMetaTags());
            }
        }
        return $tags;
    }

    /**
     * @psalm-return LinkTagsConfig
     */
    private function getLinkTags(): array
    {
        $tags = [];
        foreach ($this->injections as $injection) {
            if ($injection instanceof LinkTagsInjectionInterface) {
                $tags = array_merge($tags, $injection->getLinkTags());
            }
        }
        return $tags;
    }

    public function renderPartial(string $view, array $parameters = []): ResponseInterface
    {
        $this->view = $this->view->withContext($this);
        $content = $this->view->render($view, $parameters);

        return $this->responseFactory->createResponse($content);
    }

    public function withController(object $controller): self
    {
        $new = clone $this;
        $new->name = $this->extractControllerName($controller);
        return $new;
    }

    public function withControllerName(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    public function withViewPath(string $viewPath): self
    {
        $new = clone $this;
        $new->viewPath = $viewPath;
        return $new;
    }

    public function withViewBasePath(string $viewBasePath): self
    {
        $new = clone $this;
        $new->viewBasePath = $viewBasePath;
        return $new;
    }

    public function withLayout(?string $layout): self
    {
        $new = clone $this;
        $new->layout = $layout;
        return $new;
    }

    public function withAddedInjections(object ...$injections): self
    {
        $new = clone $this;
        $new->injections = array_merge($this->injections, $injections);
        return $new;
    }

    public function withInjections(object ...$injections): self
    {
        $new = clone $this;
        $new->injections = $injections;
        return $new;
    }

    /**
     * @psalm-param MetaTagsConfig $metaTags
     * @psalm-param LinkTagsConfig $linkTags
     */
    private function renderProxy(
        string $view,
        array $contentParameters,
        array $layoutParameters,
        array $metaTags,
        array $linkTags
    ): string {
        $this->injectMetaTags($metaTags);
        $this->injectLinkTags($linkTags);

        $this->view = $this->view->withContext($this);
        $content = $this->view->render($view, $contentParameters);

        $layout = $this->findLayoutFile($this->layout);
        if ($layout === null) {
            return $content;
        }

        $layoutParameters['content'] = $content;

        return $this->view->renderFile(
            $layout,
            $layoutParameters
        );
    }

    /**
     * @psalm-param MetaTagsConfig $tags
     */
    private function injectMetaTags(array $tags): void
    {
        /** @var mixed $tag */
        foreach ($tags as $key => $tag) {
            $key = is_string($key) ? $key : null;

            if (is_array($tag)) {
                $this->view->registerMeta($tag, $key);
                continue;
            }

            if (!($tag instanceof Meta)) {
                throw new InvalidMetaTagException($tag);
            }
            $this->view->registerMetaTag($tag, $key);
        }
    }

    /**
     * @psalm-param LinkTagsConfig $tags
     */
    private function injectLinkTags(array $tags): void
    {
        /** @var mixed $tag */
        foreach ($tags as $key => $tag) {
            if (is_array($tag)) {
                /** @var mixed */
                $position = $tag['__position'] ?? WebView::POSITION_HEAD;
                if (!is_int($position)) {
                    throw new InvalidLinkTagPositionException($position, $tag);
                }

                if (isset($tag[0]) && $tag[0] instanceof Link) {
                    $tag = $tag[0];
                } else {
                    unset($tag['__position']);
                    $tag = Html::link()->attributes($tag);
                }
            } else {
                $position = WebView::POSITION_HEAD;
                if (!($tag instanceof Link)) {
                    throw new InvalidLinkTagException($tag);
                }
            }

            $this->view->registerLinkTag($tag, $position, is_string($key) ? $key : null);
        }
    }

    private function findLayoutFile(?string $file): ?string
    {
        if ($file === null) {
            return null;
        }

        $file = $this->aliases->get($file);

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }

        return $file . '.' . $this->view->getDefaultExtension();
    }

    /**
     * Returns the controller name. Name should be converted to "id" case.
     * Method returns classname without `controller` on the ending.
     * If namespace is not contain `controller` or `controllers`
     * then returns only classname without `controller` on the ending
     * else returns all subnamespaces from `controller` (or `controllers`) to the end
     *
     * @param object $controller
     *
     * @return string
     *
     * @example App\Controller\FooBar\BazController -> foo-bar/baz
     * @example App\Controllers\FooBar\BazController -> foo-bar/baz
     * @example Path\To\File\BlogController -> blog
     *
     * @see Inflector::pascalCaseToId()
     */
    private function extractControllerName(object $controller): string
    {
        /** @var string[] $cache */
        static $cache = [];

        $class = get_class($controller);
        if (array_key_exists($class, $cache)) {
            return $cache[$class];
        }

        $regexp = '/((?<=controller\\\|s\\\)(?:[\w\\\]+)|(?:[a-z]+))controller/iuU';
        if (!preg_match($regexp, $class, $m) || empty($m[1])) {
            throw new \RuntimeException('Cannot detect controller name');
        }

        $inflector = new Inflector();
        $name = str_replace('\\', '/', $m[1]);
        return $cache[$class] = $inflector->pascalCaseToId($name);
    }
}
