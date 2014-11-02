<?php

$db = !file_exists(dirname(__FILE__) . '/local/db.php') ? require(dirname(__FILE__) . '/db.php') : require(dirname(__FILE__) . '/local/db.php');
$mail = !file_exists(dirname(__FILE__) . '/local/mail.php') ? require(dirname(__FILE__) . '/mail.php') : require(dirname(__FILE__) . '/local/mail.php');

return [
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'Информационная система мобильного реагирования учебного отдела колледжа',
    'preload' => ['log'],
    'import' => [
        'application.models.*',
        'application.components.*',
        'application.vendor.sc0rp1d.YiiMailer.YiiMailer',
    ],
    'components' => [
        'db' => $db,
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
        'authManager' => [
            'class' => 'DbAuthManager',
            'connectionID' => 'db',
            'defaultRoles' => ['guest'],
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
        'request' => [
            'hostInfo' => 'http://studyschedule.ru',
            'baseUrl' => '',
            'scriptUrl' => '',
        ],
    ],
    'params' => [
        'YiiMailer' => $mail,
        'adminEmail' => 'marklangovoi@gmail.com',
    ],
];