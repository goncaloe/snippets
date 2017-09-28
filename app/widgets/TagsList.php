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
        $reqTags = $request->get('tags');
        $reqTags = $reqTags ? preg_split('/\s*[,;]\s*/', $reqTags, null, PREG_SPLIT_NO_EMPTY) : [];

        $snippetsManager = Yii::$app->getSnippets();
        $cache = Yii::$app->getCache();

        $fw = $snippetsManager->getCurrentFramework();

        $cacheKey = 'tags.'.$fw.'.'.implode(';', $reqTags);
        $tags = $cache->get($cacheKey);

        if($tags === false){
            $list = $snippetsManager->getSnippets();
            $tags = [];
            foreach($list as $id => $s){
                if(!isset($s['framework']) || $s['framework'] !== $fw){
                    continue;
                }

                if(!empty($s['tags'])){
                    foreach($reqTags as $tag){
                        if(!in_array($tag, $s['tags'])){
                            continue 2;
                        }
                    }

                    foreach($s['tags'] as $tag) {
                        $tags[$tag] = isset($tags[$tag]) ? $tags[$tag] + 1 : 1;
                    }
                }
            }
            asort($tags);
            $tags = array_reverse($tags, true);
            $cache->set($cacheKey, $tags, 3600);
        }

        $html = '<section id="tags_wrap">';
        $html .= '<h2>tags <span class="tags-count">'.count($tags).'</span></h2>';
        $html .= '<ul class="tags-list">';
        foreach($tags as $tag => $count){
            $addTags = $reqTags;
            $isActive = in_array($tag, $reqTags);
            if($isActive){
                if (($key = array_search($tag, $addTags)) !== false) {
                    unset($addTags[$key]);
                }
            }
            else {
                $addTags[] = $tag;
                $addTags = array_unique($addTags);
            }
            $urlTag = Url::toRoute(['/snippets/list', 'tags' => empty($addTags) ? null : implode(',', $addTags)]);
            $html .= '<li'.($isActive ? ' class="active"' : '').'><a href="'.$urlTag.'">'.$tag.' <span class="count">'.$count.'</span></a></li>';
        }

        $html .= '</ul>';
        $html .= '</section>';

        echo $html;
    }
}
