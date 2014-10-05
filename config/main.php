<?php

$modules = [];

if (YII_DEBUG) {
    $modules['gii'] = [
        'class' => 'system.gii.GiiModule',
        'password' => 'kkep-schedule',
        'ipFilters' => ['127.0.0.1', '::1'],
    ];
}

$db = !file_exists(dirname(__FILE__) . '/local/db.php') ? require('db.php') : require(dirname(__FILE__) . '/local/db.php');

return [
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'language' => 'ru',
    'name' => 'Информационная система мобильного реагирования учебного отдела колледжа',
    'preload' => ['log'],
    'import' => [
        'application.models.*',
        'application.components.*',
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
        'adminEmail' => 'webmaster@example.com',
    ],
];