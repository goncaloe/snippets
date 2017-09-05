<?php

namespace app\widgets;

use Yii;
use yii\db\Query;
use yii\helpers\Url;

class TagsList extends \yii\base\Widget
{


    public function run()
    {
        $request = Yii::$app->getRequest();
        $currTag = $request->get('tag');

        //$fw = Yii::$app->getCurrentFramework();

        $query = new Query();
        $query
            ->select(['t.*', 'COUNT(st.snippet_id) AS count'])
            ->from('tags t')
            ->innerJoin('snippet_tags st', 'st.tag_id = t.id')
            //->innerJoin('snippets s', 's.id =  st.snippet_id')
            //->where(['s.fw' => $fw])
            ->groupBy('t.id');
        $tags = $query->all();

        $html = '<section id="tags_wrap">';
        $html .= '<h2>tags <span class="count">'.count($tags).'</span></h2>';
        $html .= '<ul class="tags-list">';
        foreach($tags as $tag){
            $html .= '<li'.($currTag == $tag['id'] ? ' class="active"' : '').'><a href="'.Url::toRoute(['snippets/list', 'tag' => $tag['id']]).'">'.$tag['id'].' ('.$tag['count'].')</a></li>';
        }

        $html .= '</ul>';
        $html .= '</section>';

        echo $html;
    }
}
