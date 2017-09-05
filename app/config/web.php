<?php


return [
    'id' => 'snippets-app',
    'basePath' => APP_BASE_PATH . '/app',
    'vendorPath' => APP_BASE_PATH . '/vendor',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'z9roFIhEyq4RJnCdFEMymAkJ0IkBOyuR',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    'aliases' => [
        '@snippetsUrl' => rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '\/') . '/var/snippets',
    ],
];
