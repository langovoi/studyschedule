<?php

date_default_timezone_set('Europe/Moscow');

if(file_exists(dirname(__FILE__) . '/../.update')) {
    include dirname(__FILE__) . '/../static/update.html';
    die;
}

if (file_exists(dirname(__FILE__) . '/../.local')) {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);
}

$yii = dirname(__FILE__) . '/../vendor/yiisoft/yii/framework/YiiBase.php';
$config = dirname(__FILE__) . '/../config/main.php';

require_once($yii);

class Yii extends YiiBase
{
    /**
     * @static
     * @return CWebApplication
     */
    public static function app()
    {
        return parent::app();
    }
}

Yii::createWebApplication($config)->run();