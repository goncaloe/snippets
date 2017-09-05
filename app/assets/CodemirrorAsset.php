<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

class CodemirrorAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/codemirror';
    public $baseUrl = '@web';

    public $css = [
        'css/codemirror.css'
    ];

    public $js = [
        'js/codemirror.js',
        'js/xml/xml.js',
        'js/javascript/javascript.js',
        'js/css/css.js',
        'js/htmlmixed/htmlmixed.js'
    ];

}