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
    public $sourcePath = '@bower/codemirror';
    public $baseUrl = '@web';

    public $css = [
        'lib/codemirror.css',
    ];

    public $js = [
        'lib/codemirror.js',

        'mode/css/css.js',
        'mode/javascript/javascript.js',
        'mode/php/php.js',
        'mode/sass/sass.js',
        'mode/xml/xml.js',
        'mode/htmlmixed/htmlmixed.js',

        'addon/fold/xml-fold.js',
        'addon/edit/matchbrackets.js',
        'addon/edit/closebrackets.js',
        'addon/edit/closetag.js',
    ];

}