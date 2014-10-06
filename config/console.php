<?php

$db = !file_exists(dirname(__FILE__) . '/local/db.php') ? require(dirname(__FILE__) . '/db.php') : require(dirname(__FILE__) . '/local/db.php');

return [
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'Информационная система мобильного реагирования учебного отдела колледжа',
    'preload' => ['log'],
    'import' => [
        'application.models.*',
        'application.components.*',
    ],
    'components' => [
        'db' => $db,
        'errorHandler' => [
            'errorAction' => 'site/error',
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
    ],
];