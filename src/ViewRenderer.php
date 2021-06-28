<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Link;
use Yiisoft\Html\Tag\Meta;
use Yiisoft\Strings\Inflector;
use Yiisoft\View\ViewContextInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\Exception\InvalidLinkTagException;
use Yiisoft\Yii\View\Exception\InvalidMetaTagException;

use function array_key_exists;
use function array_merge;
use function get_class;
use function gettype;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function pathinfo;
use function preg_match;
use function rtrim;
use function sprintf;
use function str_replace;

/**
 * ViewRenderer renders the view and places it in the response instance {@see \Psr\Http\Message\ResponseInterface}.
 *
 * @psalm-import-type MetaTagsConfig from MetaTagsInjectionInterface
 * @psalm-import-type LinkTagsConfig from LinkTagsInjectionInterface
 */
final class ViewRenderer implements ViewContextInterface
{
    private DataResponseFactoryInterface $responseFactory;
    private Aliases $aliases;
    private WebView $view;

    private string $viewPath;
    private ?string $layout;
    private ?string $name = null;

    /**
     * @var object[]
     */
    private array $injections;

    /**
     * @param DataResponseFactoryInterface $responseFactory The data response factory instance.
     * @param Aliases $aliases The aliases instance.
     * @param WebView $view The web view instance.
     * @param string $viewPath The full path to the directory of views.
     * @param string|null $layout The layout name (e.g. "layout/main") to be applied to views.
     * If null, the layout will not be applied.
     * @param object[] $injections The injection instances.
     */
    public function __construct(
        DataResponseFactoryInterface $responseFactory,
        Aliases $aliases,
        WebView $view,
        string $viewPath,
        ?string $layout = null,
        array $injections = []
    ) {
        $this->responseFactory = $responseFactory;
        $this->aliases = $aliases;
        $this->view = $view;
        $this->viewPath = rtrim($viewPath, '/');
        $this->layout = $layout;
        $this->injections = $injections;
    }

    /**
     * Returns a path to the directory of views that is a prefix to the relative view name.
     *
     * If a controller name has been set {@see withController(), withControllerName()}, it will be appended to the path.
     *
     * @return string The path to the directory of views that is a prefix to the relative view name.
     */
    public function getViewPath(): string
    {
        return $this->aliases->get($this->viewPath) . ($this->name ? '/' . $this->name : '');
    }

    /**
     * Renders a view and places it in the response instance.
     *
     * @param string $view The view name.
     * @param array $parameters The parameters (name-value pairs) that will be extracted
     * and made available in the view file.
     *
     * @return ResponseInterface The response instance.
     */
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

    /**
     * Renders a view without applying a layout and places it in the response instance.
     *
     * @param string $view The view name.
     * @param array $parameters The parameters (name-value pairs) that will be extracted
     * and made available in the view file.
     *
     * @return ResponseInterface The response instance.
     */
    public function renderPartial(string $view, array $parameters = []): ResponseInterface
    {
        if ($this->layout === null) {
            return $this->render($view, $parameters);
        }

        return $this->withLayout(null)->render($view, $parameters);
    }

    /**
     * Extracts the controller name and returns a new instance with the controller name.
     *
     * @param object $controller The controller instance.
     *
     * @return self
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
     *
     * @return self
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
     * @param string $viewPath The full path to the directory of views.
     *
     * @return self
     */
    public function withViewPath(string $viewPath): self
    {
        $new = clone $this;
        $new->viewPath = rtrim($viewPath, '/');
        return $new;
    }

    /**
     * Returns a new instance with the specified layout.
     *
     * @param string|null $layout The layout name (e.g. "layout/main") to be applied to views.
     * If null, the layout will not be applied.
     *
     * @return self
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
     * @param object ...$injections The injection instances.
     *
     * @return self
     */
    public function withAddedInjections(object ...$injections): self
    {
        $new = clone $this;
        $new->injections = array_merge($this->injections, $injections);
        return $new;
    }

    /**
     * Returns a new instance with the specified injections.
     *
     * @param object ...$injections The injection instances.
     *
     * @return self
     */
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
        return $this->view->renderFile($layout, $layoutParameters);
    }

    private function getContentParameters(array $renderParameters): array
    {
        $parameters = [];
        foreach ($this->injections as $injection) {
            if ($injection instanceof ContentParametersInjectionInterface) {
                $parameters = array_merge($parameters, $injection->getContentParameters());
            }
        }
        return array_merge($parameters, $renderParameters);
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
                throw new InvalidMetaTagException(
                    sprintf(
                        'Meta tag in injection should be instance of %s or an array. Got %s.',
                        Meta::class,
                        $this->getType($tag),
                    ),
                    $tag
                );
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
                    $tag = Html::link()->attributes($tag);
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
            throw new RuntimeException('Cannot detect controller name.');
        }

        $inflector = new Inflector();
        $name = str_replace('\\', '/', $m[1]);
        return $cache[$class] = $inflector->pascalCaseToId($name);
    }

    /**
     * @param mixed $value
     */
    private function getType($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
