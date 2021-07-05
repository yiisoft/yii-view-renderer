<?php

/**
 * @var Yiisoft\View\WebView $this
 * @var string $content
 * @var string $footer
 */

$this->beginPage();
?><html>
<head><?php $this->head() ?></head>
<body>
    <p><?= $content ?></p>
    <div><?= $footer ?></div>
    <?php $this->endBody() ?>
</body>
</html><?php
$this->endPage();
