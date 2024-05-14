<?php

declare(strict_types=1);

/**
 * @var Yiisoft\View\WebView $this
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
