<?php

namespace app\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class ThemeSelect extends \yii\base\Widget
{

    public $frameworkLabels = [
        'bs3' => 'Bootstrap 3',
        'bs4' => 'Bootstrap 4',
    ];

    public function run()
    {
        $snippets = Yii::$app->getSnippets();

        $currTheme = $snippets->getCurrentTheme();
        $themes = $snippets->getThemes();

        $options = [];
        foreach($themes as $themeId => $data){
            $fw = $data['framework'];
            $group = isset($this->frameworkLabels[$fw]) ? $this->frameworkLabels[$fw] : $fw;
            $options[$group][$themeId] = $data['name'];
        }

        $html = '<section id="theme_select_wrap">';
        $html .= '<label>Theme</label> ';
        $html .= '<select id="theme_select">';
        $html .= Html::renderSelectOptions($currTheme, $options);
        $html .= '</select>';
        $html .= '</section>';

        $this->registerClientScript();

        echo $html;
    }

    private function registerClientScript(){
        $url = Url::toRoute(['snippets/list', 'theme' => '__theme__']);
        $this->getView()->registerJs("
            $('#theme_select').on('change', function(){
                var url = '{$url}';
                url = url.replace('__theme__', $(this).val());
                window.location.replace(url);
            });
        ");
    }
}
