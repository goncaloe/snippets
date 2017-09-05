<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/app';
    public $baseUrl = '@web';

    public $css = [
        'css/site.css',
    ];

    public $js = [
        'js/iframeresizer.min.js',
    ];

    public function publish($am)
    {
        if(YII_ENV_DEV) {
            $source = realpath($this->sourcePath);
            $hash = sprintf('%x', crc32($source . filemtime($source) . \Yii::getVersion()));

            $watchFiles = [];
            foreach(array_merge($this->js, $this->css) as $file){
                if(($pos = strpos($file, '?')) !== false){
                    $watchFiles[] = substr($file, 0, $pos);
                }
                else {
                    $watchFiles[] = $file;
                }
            }

            foreach($watchFiles as $watchFile){
                $sourceFile = $source . '/' . $watchFile;
                $destFile = $am->basePath . '/' . $hash . '/' . $watchFile;
                if (file_exists($destFile) && filemtime($destFile) < filemtime($sourceFile)) {
                    $this->publishOptions['forceCopy'] = true;
                    break;
                }
            }

        }
        return parent::publish($am);
    }

}