<?php

if (file_exists('.local')) {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);
}

$yii = dirname(__FILE__) . '/../vendor/yiisoft/yii/framework/YiiBase.php';
$config = dirname(__FILE__) . '/protected/config/main.php';

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