<?php

declare(strict_types=1);
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var string $content
 */

$this->beginPage();
?>
<html>
<head><?php $this->head() ?></head>
<body>
<?php $this->beginBody(); ?>
<?= $content ?>
<?php $this->endBody() ?>
</body>
</html><?php
$this->endPage();
