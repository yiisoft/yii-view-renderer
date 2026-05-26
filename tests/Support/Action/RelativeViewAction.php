<?php

declare(strict_types=1);

namespace Yiisoft\Yii\View\Renderer\Tests\Support\Action;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class RelativeViewAction
{
    public function render(WebViewRenderer $renderer): ResponseInterface
    {
        return $renderer->render('template', ['name' => 'render']);
    }

    public function renderPartial(WebViewRenderer $renderer): ResponseInterface
    {
        return $renderer->renderPartial('template', ['name' => 'renderPartial']);
    }

    public function renderAsString(WebViewRenderer $renderer): string
    {
        return $renderer->renderAsString('template', ['name' => 'renderAsString']);
    }

    public function renderPartialAsString(WebViewRenderer $renderer): string
    {
        return $renderer->renderPartialAsString('template', ['name' => 'renderPartialAsString']);
    }
}
