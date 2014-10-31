<?php

$modules = [];

if (YII_DEBUG) {
    $modules['gii'] = [
        'class' => 'system.gii.GiiModule',
        'password' => 'kkep-schedule',
        'ipFilters' => ['127.0.0.1', '::1'],
    ];
}

$db = !file_exists(dirname(__FILE__) . '/local/db.php') ? require(dirname(__FILE__) . '/db.php') : require(dirname(__FILE__) . '/local/db.php');
$mail = !file_exists(dirname(__FILE__) . '/local/mail.php') ? require(dirname(__FILE__) . '/mail.php') : require(dirname(__FILE__) . '/local/mail.php');

return [
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'language' => 'ru',
    'name' => 'Информационная система мобильного реагирования учебного отдела колледжа',
    'preload' => ['log'],
    'import' => [
        'application.models.*',
        'application.components.*',
        'application.vendor.sc0rp1d.YiiMailer.YiiMailer',
        'bootstrap.behaviors.*',
        'bootstrap.helpers.*',
        'bootstrap.widgets.*',
        'highcharts.*',
    ],
    'aliases' => [
        'Jsvrcek' => 'application.vendor.jsvrcek.ics.src.Jsvrcek',
        'bootstrap' => 'application.vendor.drmabuse.yii-bootstrap-3-module',
        'highcharts' => 'application.vendor.miloschuman.yii-highcharts.highcharts',
    ],
    'modules' => $modules,
    'components' => [
        'viewRenderer' => [
            'class' => 'application.vendor.yiiext.twig-renderer.ETwigViewRenderer',
            'twigPathAlias' => 'application.vendor.twig.twig.lib.Twig',
            'functions' => [
                'dump' => 'var_dump',
                'get_class' => 'get_class'
            ],
        ],
        'user' => [
            'allowAutoLogin' => true,
        ],
        'authManager' => [
            'class' => 'DbAuthManager',
            'connectionID' => 'db',
            'defaultRoles' => ['guest'],
        ],
        'urlManager' => [
            'urlFormat' => 'path',
            'showScriptName' => false,
            'caseSensitive' => false,
            'rules' => [
                '<action:(login|logout)>' => 'site/<action>',
                'dashboard' => 'site/dashboard',
                'invite' => 'site/invite',
                'ics/<id:\d+>' => 'ics/group',
                'ics/<id:\d+>.ics' => 'ics/group',
                'group/<id:\d+>/<action:\w+>' => 'group/<action>',
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
        'db' => $db,
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'class' => 'CLogRouter',
            'routes' => [
                [
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ],
            ],
        ],
    ],
    'params' => [
        'YiiMailer' => $mail,
        'adminEmail' => 'marklangovoi@gmail.com',
    ],
];