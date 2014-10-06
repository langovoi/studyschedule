<?php

// change the following paths if necessary
$yiic = dirname(__FILE__) . '/vendor/yiisoft/yii/framework/yiic.php';

if (file_exists(dirname(__FILE__) . '/.local')) {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);
}

$config = dirname(__FILE__) . '/config/console.php';

require_once($yiic);
