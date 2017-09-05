<?php

namespace app\widgets;

use Yii;
use yii\db\Query;
use yii\helpers\Url;

class FilterTags extends \yii\base\Widget
{

    public function run()
    {
        //$request = Yii::$app->getRequest();
        $html = '<section id="filter_wrap">';
        $html .= '<input type="text" placeholder="Filter">';
        $html .= '</section>';

        echo $html;
    }
}
