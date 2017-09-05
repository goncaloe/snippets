<?php

namespace app\helpers;

use Yii;

class SnippetsHelper
{

    public static function getThemeMeta($theme){
        $metaFile = Yii::getAlias('@var/themes/'.$theme.'/meta.json');
        if(is_file($metaFile)){
            $content = file_get_contents($metaFile);
            $res = json_decode($content, true);
            return $res;
        }
        return [];
    }
}
