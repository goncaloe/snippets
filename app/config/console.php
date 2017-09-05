<?php


$config = [
    'id' => 'snippets-console',
    'basePath' => APP_BASE_PATH . '/app',
    'vendorPath' => APP_BASE_PATH . '/vendor',
    'controllerNamespace' => 'app\commands',
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

return $config;
