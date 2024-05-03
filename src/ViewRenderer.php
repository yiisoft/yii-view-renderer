<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

use LogicException;
use RuntimeException;
use Throwable;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Link;
use Yiisoft\Html\Tag\Meta;
use Yiisoft\Strings\Inflector;
use Yiisoft\View\Exception\ViewNotFoundException;
use Yiisoft\View\ViewContextInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\Exception\InvalidLinkTagException;
use Yiisoft\Yii\View\Exception\InvalidMetaTagException;

use function array_key_exists;
use function array_merge;
use function is_array;
use function is_int;
use function is_string;
use function pathinfo;
use function preg_match;
use function rtrim;
use function sprintf;
use function str_replace;

/**
 * ViewRenderer renders the view.
 *
 * If {@see ViewRenderer::render()} or {@see ViewRenderer::renderPartial()} methods are called,
 * an instance of {@see DataResponse} is returned. It supports deferred rendering that
 * occurs when calling {@see DataResponse::getBody()} or {@see DataResponse::getData()}.
 *
 * If {@see ViewRenderer::renderAsString()} or {@see ViewRenderer::renderPartialAsString()} methods are called,
 * the rendering will occur immediately and the string result of the rendering will be returned.
 */
final class ViewRenderer implements ViewContextInterface
{
    private ?string $viewPath = null;
    private ?string $name = null;
    private ?string $locale = null;

    /**
     * @var object[]|null
     */
    private ?array $preparedInjections = null;

    private InjectionContainerInterface $injectionContainer;

    /**
     * @param DataResponseFactoryInterface $responseFactory The data response factory instance.
     * @param Aliases $aliases The aliases instance.
     * @param WebView $view The web view instance.
     * @param string|null $viewPath The full path to the directory of views or its alias.
     * @param string|null $layout The layout name (e.g. "layout/main") to be applied to views.
     * If null, the layout will not be applied.
     * @param array $injections The injection instances or class names.
     *
     * @psalm-param array<object|string> $injections
     */
    public function __construct(
        private DataResponseFactoryInterface $responseFactory,
        private Aliases $aliases,
        private WebView $view,
        ?string $viewPath = null,
        private ?string $layout = null,
        private array $injections = [],
        ?InjectionContainerInterface $injectionContainer = null,
    ) {
        $this->injectionContainer = $injectionContainer ??
            new class () implements InjectionContainerInterface {
                public function get(string $id): object
                {
                    throw new RuntimeException('Injections container is not set.');
                }
            };
        $this->setViewPath($viewPath);
    }

    /**
     * Returns a path to a base directory of view templates that is prefixed to the relative view name.
     *
     * If a controller name has been set {@see withController(), withControllerName()}, it will be appended to the path.
     *
     * @return string View templates base directory.
     */
    public function getViewPath(): string
    {
        if ($this->viewPath === null) {
            throw new LogicException('The view path is not set.');
        }

        return $this->aliases->get($this->viewPath) . ($this->name ? '/' . $this->name : '');
    }

    /**
     * Returns a response instance {@see DataResponse} that supports deferred rendering.
     *
     * Rendering will occur when calling {@see DataResponse::getBody()} or {@see DataResponse::getData()}.
     *
     * @param string $view The view name {@see WebView::render()}.
     * @param array $parameters The parameters (name-value pairs) that will be extracted
     * and made available in the view file.
     *
     * @psalm-param array<string, mixed> $parameters
     *
     * @return DataResponse The response instance.
     */
    public function render(string $view, array $parameters = []): DataResponse
    {
        $commonParameters = $this->getCommonParameters();
        $layoutParameters = $this->getLayoutParameters();
        $metaTags = $this->getMetaTags();
        $linkTags = $this->getLinkTags();

        return $this->responseFactory->createResponse(fn (): string => $this->renderProxy(
            $view,
            $parameters,
            $commonParameters,
            $layoutParameters,
            $metaTags,
            $linkTags,
        ));
    }

    /**
     * Returns a response instance {@see DataResponse} that supports deferred
     * rendering {@see render()} without applying a layout.
     *
     * Rendering will occur when calling {@see DataResponse::getBody()} or {@see DataResponse::getData()}.
     *
     * @param string $view The view name {@see WebView::render()}.
     * @param array $parameters The parameters (name-value pairs) that will be extracted
     * and made available in the view file.
     *
     * @psalm-param array<string, mixed> $parameters
     *
     * @return DataResponse The response instance.
     */
    public function renderPartial(string $view, array $parameters = []): DataResponse
    {
        if ($this->layout === null) {
            return $this->render($view, $parameters);
        }

        return $this
            ->withLayout(null)
            ->render($view, $parameters);
    }

    /**
     * Renders a view as a string.
     *
     * @param string $view The view name {@see WebView::render()}.
     * @param array $parameters The parameters (name-value pairs) that will be extracted
     * and made available in the view file.
     *
     * @psalm-param array<string, mixed> $parameters
     *
     * @throws RuntimeException If the view cannot be resolved.
     * @throws Throwable If an error occurred during rendering.
     * @throws ViewNotFoundException If the view file does not exist.
     *
     * @return string The rendering result.
     */
    public function renderAsString(string $view, array $parameters = []): string
    {
        return $this->renderProxy(
            $view,
            $parameters,
            $this->getCommonParameters(),
            $this->getLayoutParameters(),
            $this->getMetaTags(),
            $this->getLinkTags(),
        );
    }

    /**
     * Renders a view as string {@see renderAsString()} without applying a layout.
     *
     * @param string $view The view name {@see WebView::render()}.
     * @param array $parameters The parameters (name-value pairs) that will be extracted
     * and made available in the view file.
     *
     * @psalm-param array<string, mixed> $parameters
     *
     * @throws RuntimeException If the view cannot be resolved.
     * @throws Throwable If an error occurred during rendering.
     * @throws ViewNotFoundException If the view file does not exist.
     *
     * @return string The rendering result.
     */
    public function renderPartialAsString(string $view, array $parameters = []): string
    {
        if ($this->layout === null) {
            return $this->renderAsString($view, $parameters);
        }

        return $this
            ->withLayout(null)
            ->renderAsString($view, $parameters);
    }

    /**
     * Extracts the controller name and returns a new instance with the controller name.
     *
     * @param object $controller The controller instance.
     */
    public function withController(object $controller): self
    {
        $new = clone $this;
        $new->name = $this->extractControllerName($controller);
        return $new;
    }

    /**
     * Returns a new instance with the specified controller name.
     *
     * @param string $name The controller name.
     */
    public function withControllerName(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    /**
     * Returns a new instance with the specified view path.
     *
     * @param string $viewPath The full path to the directory of views or its alias.
     */
    public function withViewPath(string $viewPath): self
    {
        $new = clone $this;
        $new->setViewPath($viewPath);
        return $new;
    }

    /**
     * Returns a new instance with the specified layout.
     *
     * @param string|null $layout The layout name (e.g. "layout/main") to be applied to views.
     * If null, the layout will not be applied.
     */
    public function withLayout(?string $layout): self
    {
        $new = clone $this;
        $new->layout = $layout;
        return $new;
    }

    /**
     * Return a new instance with the appended specified injections.
     *
     * @param object|string ...$injections The injection instances or class names.
     */
    public function withAddedInjections(object|string ...$injections): self
    {
        $new = clone $this;
        $new->setInjections(array_merge($this->injections, $injections));
        return $new;
    }

    /**
     * Returns a new instance with the specified injections.
     *
     * @param object|string ...$injections The injection instances or class names.
     */
    public function withInjections(object|string ...$injections): self
    {
        $new = clone $this;
        $new->setInjections($injections);
        return $new;
    }

    /**
     * Returns a new instance with specified locale code.
     *
     * @param string $locale The locale code.
     */
    public function withLocale(string $locale): self
    {
        $new = clone $this;
        $new->locale = $locale;
        return $new;
    }

    /**
     * Renders a view as a string injecting parameters and tags into view context.
     *
     * @param string $view The view name {@see WebView::render()}.
     * @param array $contentParameters The content parameters to render view.
     * @param array $injectCommonParameters The common parameters to inject.
     * @param array $injectLayoutParameters The layout parameters to inject.
     * @param array $metaTags The meta tags to inject.
     * @param array $linkTags The link tags to inject.
     *
     * @psalm-param array<string, mixed> $contentParameters
     * @psalm-param array<string, mixed> $injectCommonParameters
     * @psalm-param array<string, mixed> $injectLayoutParameters
     *
     * @throws RuntimeException If the view cannot be resolved.
     * @throws Throwable If an error occurred during rendering.
     * @throws ViewNotFoundException If the view file does not exist.
     *
     * @return string The rendering result.
     */
    private function renderProxy(
        string $view,
        array $contentParameters,
        array $injectCommonParameters,
        array $injectLayoutParameters,
        array $metaTags,
        array $linkTags
    ): string {
        $currentView = $this->view->withContext($this);

        if ($this->locale !== null) {
            $currentView = $currentView->withLocale($this->locale);
        }

        $this->injectMetaTags($metaTags, $currentView);
        $this->injectLinkTags($linkTags, $currentView);

        $content = $currentView
            ->setParameters($injectCommonParameters)
            ->render($view, $contentParameters);

        if ($this->layout === null) {
            return $content;
        }

        $layout = $this->findLayoutFile($this->layout, $currentView);

        $layoutParameters = array_filter(
            $injectLayoutParameters,
            /** @psalm-suppress MissingClosureParamType */
            static fn ($_value, string $key): bool => !$currentView->hasParameter($key),
            ARRAY_FILTER_USE_BOTH,
        );

        return $currentView
            ->setParameters($layoutParameters)
            ->renderFile($layout, ['content' => $content]);
    }

    /**
     * Gets injection common parameters merged with parameters specified during rendering.
     *
     * The parameters specified during rendering have more priority and will
     * overwrite the injected common parameters if their names match.
     *
     * @return array The injection common parameters merged with the parameters specified during rendering.
     *
     * @psalm-return array<string, mixed>
     */
    private function getCommonParameters(): array
    {
        $parameters = [];
        foreach ($this->getInjections(CommonParametersInjectionInterface::class) as $injection) {
            $parameters = array_merge($parameters, $injection->getCommonParameters());
        }
        return $parameters;
    }

    /**
     * Gets the merged injection layout parameters.
     *
     * @return array The merged injection layout parameters.
     *
     * @psalm-return array<string, mixed>
     */
    private function getLayoutParameters(): array
    {
        $parameters = [];
        foreach ($this->getInjections(LayoutParametersInjectionInterface::class) as $injection) {
            $parameters = array_merge($parameters, $injection->getLayoutParameters());
        }
        return $parameters;
    }

    /**
     * Gets the merged injection meta tags.
     *
     * @return array The merged injection meta tags.
     */
    private function getMetaTags(): array
    {
        $tags = [];
        foreach ($this->getInjections(MetaTagsInjectionInterface::class) as $injection) {
            $tags = array_merge($tags, $injection->getMetaTags());
        }
        return $tags;
    }

    /**
     * Gets the merged injection link tags.
     *
     * @return array The merged injection link tags.
     */
    private function getLinkTags(): array
    {
        $tags = [];
        foreach ($this->getInjections(LinkTagsInjectionInterface::class) as $injection) {
            $tags = array_merge($tags, $injection->getLinkTags());
        }
        return $tags;
    }

    /**
     * @psalm-template T
     * @psalm-param class-string<T> $injectionInterface
     * @psalm-return list<T>
     */
    private function getInjections(string $injectionInterface): array
    {
        $result = [];
        foreach ($this->getPreparedInjections() as $injection) {
            if ($injection instanceof $injectionInterface) {
                $result[] = $injection;
            }
        }
        return $result;
    }

    /**
     * Injects meta tags to the view.
     *
     * @param array $tags The meta tags to inject.
     *
     * @see WebView::registerMeta()
     * @see WebView::registerMetaTag()
     */
    private function injectMetaTags(array $tags, WebView $view): void
    {
        foreach ($tags as $key => $tag) {
            $key = is_string($key) ? $key : null;

            if (is_array($tag)) {
                $view->registerMeta($tag, $key);
                continue;
            }

            if (!($tag instanceof Meta)) {
                throw new InvalidMetaTagException(
                    sprintf(
                        'Meta tag in injection should be instance of %s or an array. Got %s.',
                        Meta::class,
                        $this->getType($tag),
                    ),
                    $tag
                );
            }

            $view->registerMetaTag($tag, $key);
        }
    }

    /**
     * Injects link tags to the view.
     *
     * @param array $tags The link tags to inject.
     *
     * @see WebView::registerLinkTag()
     */
    private function injectLinkTags(array $tags, WebView $view): void
    {
        foreach ($tags as $key => $tag) {
            if (is_array($tag)) {
                $position = $tag['__position'] ?? WebView::POSITION_HEAD;
                if (!is_int($position)) {
                    throw new InvalidLinkTagException(
                        sprintf(
                            'Link tag position in injection should be integer. Got %s.',
                            $this->getType($position),
                        ),
                        $tag
                    );
                }

                if (isset($tag[0]) && $tag[0] instanceof Link) {
                    $tag = $tag[0];
                } else {
                    unset($tag['__position']);
                    $tag = Html::link()->addAttributes($tag);
                }
            } else {
                $position = WebView::POSITION_HEAD;
                if (!($tag instanceof Link)) {
                    throw new InvalidLinkTagException(
                        sprintf(
                            'Link tag in injection should be instance of %s or an array. Got %s.',
                            Link::class,
                            $this->getType($tag),
                        ),
                        $tag
                    );
                }
            }

            $view->registerLinkTag($tag, $position, is_string($key) ? $key : null);
        }
    }

    /**
     * Finds a layout file based on the given file path or alias.
     *
     * @param string $file The file path or alias.
     *
     * @return string The path to the file with the file extension.
     */
    private function findLayoutFile(string $file, WebView $view): string
    {
        $file = $this->aliases->get($file);

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }

        $layoutFile = $file . '.' . $view->getDefaultExtension();

        if ($view->getDefaultExtension() !== 'php' && !is_file($layoutFile)) {
            $layoutFile = $file . '.php';
        }

        return $layoutFile;
    }

    /**
     * Returns a controller name based on controller instance.
     *
     * Name should be converted to "id" case without `controller` on the ending.
     *
     * If namespace does not contain `controller` or `controllers` then the method returns only classname without
     * `controller` at the end else it returns all sub-namespaces with `controller` (or `controllers`) at the end.
     *
     * @param object $controller The controller instance.
     *
     * @return string The controller name.
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

        $class = $controller::class;
        if (array_key_exists($class, $cache)) {
            return $cache[$class];
        }

        $regexp = '/((?<=controller\\\|controllers\\\)(?:[\w\\\]+)|(?:[a-z\d]+))controller$/iU';
        if (!preg_match($regexp, $class, $m) || empty($m[1])) {
            throw new RuntimeException('Cannot detect controller name.');
        }

        $inflector = new Inflector();
        $name = str_replace('\\', '/', $m[1]);
        return $cache[$class] = $inflector->pascalCaseToId($name);
    }

    /**
     * Returns the value type.
     *
     * @param mixed $value The value to check.
     *
     * @return string The value type.
     */
    private function getType(mixed $value): string
    {
        return get_debug_type($value);
    }

    private function setViewPath(?string $path): void
    {
        $this->viewPath = $path === null ? null : rtrim($path, '/');
    }

    private function getPreparedInjections(): array
    {
        if ($this->preparedInjections !== null) {
            return $this->preparedInjections;
        }

        $this->preparedInjections = [];
        foreach ($this->injections as $injection) {
            $this->preparedInjections[] = is_object($injection)
                ? $injection
                : $this->injectionContainer->get($injection);
        }

        return $this->preparedInjections;
    }

    /**
     * @psalm-param array<object|string> $injections
     */
    private function setInjections(array $injections): void
    {
        $this->injections = $injections;
        $this->preparedInjections = null;
    }
}
