<?php
/**
 * Yii bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require(APP_BASE_PATH . '/vendor/yiisoft/yii2/BaseYii.php');

/**
 *
 * @property \yii\console\Application|\app\Application $app the application instance. This property is read-only.
 */
class Yii extends \yii\BaseYii
{

}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = include(YII2_PATH . '/classes.php');
Yii::$container = new yii\di\Container;

Yii::setAlias('@app', APP_BASE_PATH . '/app');
Yii::setAlias('@console', APP_BASE_PATH . '/console');
Yii::setAlias('@data', APP_BASE_PATH.'/data');

if(!function_exists('p')){
    function p($a, $d = 5){
        if(YII_DEBUG) {
            $code = yii\helpers\VarDumper::dumpAsString($a, $d, true);
            $code = '<code class="print">'.substr($code, 6);
            echo $code;
        }
    }
}