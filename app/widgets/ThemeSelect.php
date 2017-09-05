<?php

namespace app\widgets;

use Yii;
use yii\db\Query;
use yii\helpers\Url;

class ThemeSelect extends \yii\base\Widget
{


    public function run()
    {
        $url = Url::toRoute(['snippets/list', 'theme' => '__theme__']);
        $this->getView()->registerJs("
                            $('#theme_select').on('change', function(){
                                var url = '{$url}';
                                url = url.replace('__theme__', $(this).val());
                                window.location.replace(url);
                            });
                        ");

        $currTheme = Yii::$app->getCurrentTheme();

        $html = '<section id="theme_select_wrap">';
        $html .= '<label>Theme</label> ';
        $html .= '<select id="theme_select">';
        foreach(Yii::$app->getThemes() as $theme => $name) {
            $html .= '<option value="' . $theme . '"'. ($theme == $currTheme ? ' selected' : '') . '>' . $name . '</option>';
        }
        $html .= '</select>';
        $html .= '</section>';

        echo $html;
    }
}
