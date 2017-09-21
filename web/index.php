<?php

defined('APP_BASE_PATH') or define('APP_BASE_PATH', dirname(__DIR__));
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(APP_BASE_PATH . '/vendor/autoload.php');
require(APP_BASE_PATH . '/app/Yii.php');

$config = require(APP_BASE_PATH . '/app/config/web.php');
if(file_exists(APP_BASE_PATH . '/app/config/local.php')){
    $config = yii\helpers\ArrayHelper::merge($config, require(APP_BASE_PATH . '/app/config/local.php'));
}

(new app\Application($config))->run();
