<?php

/**
 * @var $this \Yiisoft\View\WebView
 * @var $content string
 * @var $footer string
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
