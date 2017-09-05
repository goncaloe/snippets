<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\base\Exception;
use yii\web\HttpException;
use yii\db\Query;
use yii\data\Pagination;
use app\helpers\SnippetsHelper;
use yii\helpers\Url;
use yii\base\DynamicModel;

class SnippetsController extends Controller
{

    public $defaultAction = 'list';

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionList()
    {
        $request = Yii::$app->getRequest();

        if($theme = $request->get('theme')){
            Yii::$app->setCurrentTheme($theme);
        }

        $query = new Query();
        $query->from('snippets s');

        if($tag = $request->get('tag')){
            $query->innerJoin('snippet_tags t', 't.snippet_id = s.id AND t.tag_id = :tag', [':tag' => $tag]);
        }

        $count = $query->count();

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $count,
        ]);

        $snippets = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->indexBy('id')
            ->all();

        foreach($snippets as $i => $snippet){
            $snippets[$i]['tags'] = (new Query())
                ->from('snippet_tags t')
                ->where(['t.snippet_id' => $snippet['id']])
                ->all();
        }




        return $this->render('list', [
            'snippets' => $snippets,
            'pagination' => $pagination,
        ]);
    }

    public function actionView($id)
    {
        $snippet = (new Query())
            ->from('snippets s')
            ->where(['id' => $id])
            ->one();
        if(!$snippet){
            throw new HttpException(404, 'Snippet does not exists.');
        }

        $snippetPath = Yii::getAlias('@var/snippets').'/'.$id;

        $indexFile = $snippetPath.'/index.html';
        if(!file_exists($indexFile)){
            throw new Exception("File iframe index '$indexFile' not exists.");
        }

        $contentHtml = file_get_contents($indexFile);

        $jsFile = $snippetPath.'/index.js';
        $contentJs = false;
        if(file_exists($jsFile)){
            $contentJs = file_get_contents($jsFile);
        }

        $cssFile = $snippetPath.'/index.css';
        $contentCss = false;
        if(file_exists($cssFile)){
            $contentCss = file_get_contents($cssFile);
        }


        //$meta = $this->getMeta($snippetPath);

        $tags = (new Query())
            ->from('snippet_tags t')
            ->where(['t.snippet_id' => $snippet['id']])
            ->all();

        return $this->render('view', [
            'snippet' => $snippet,
            'tags' => $tags,
            'contentHtml' => $contentHtml,
            'contentJs' => $contentJs,
            'contentCss' => $contentCss,
        ]);
    }


    public function actionIframe($id)
    {
        return $this->renderIframe($id);
    }


    public function actionTags()
    {
        $query = new Query();
        $query
            ->select(['t.*', 'COUNT(st.snippet_id) AS count'])
            ->from('tags t')
            ->leftJoin('snippet_tags st', 'st.tag_id = t.id')
            ->groupBy('t.id');
        $tags = $query->all();

        return $this->render('tags', [
            'tags' => $tags,
        ]);
    }

    public function actionCreate()
    {
        $request = Yii::$app->getRequest();

        $model = new DynamicModel(['id', 'name', 'tags']);
        $model->addRule('id', 'string', ['min' => 5, 'max' => 128])
            ->addRule('name', 'string', ['max' => 128])
            ->addRule('tags', 'string');

        if($model->load($request->post()) && $model->validate()){
            $id = $model->id;
            $snippetsPath = Yii::getAlias('@var/snippets');
            $snippetPath = $snippetsPath .'/' . $model->id;
            if(!is_dir($snippetPath)){
                mkdir($snippetPath);
                file_put_contents($snippetPath.'/index.html', '<!-- File generated -->');

                file_put_contents($snippetPath.'/meta.json', '{
    "name": "'.$id.'",
    "tags": [],
    "date": "'.date("Y-m-d H:i:s").'",
    "framework": "bs3",
    "url": ""
}');


                return $this->redirect(['snippets/view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'snippet' => $model,
        ]);
    }


    public function renderIframe($id, $params = [])
    {
        $snippetPath = Yii::getAlias('@var/snippets').'/'.$id;
        $snippetUrl = Yii::getAlias('@snippetsUrl').'/'.$id;
        $indexFile = $snippetPath.'/index.html';

        if(!file_exists($indexFile)){
            throw new Exception("File iframe index '$indexFile' not exists.");
        }

        $am = Yii::$app->getAssetManager();

        $content = file_get_contents($indexFile);
        $content = str_replace('{snippetUrl}', $snippetUrl, $content);

        $js = [];
        $css = [];

        $currTheme = Yii::$app->getCurrentTheme();
        $themePath = Yii::getAlias('@var/themes/'.$currTheme);
        $publish = $am->publish($themePath);
        $themeUrl = $publish[1];

        $metaFile = $themePath.'/meta.json';
        $meta = [];
        if(is_file($metaFile)){
            $mc = file_get_contents($metaFile);
            $meta = json_decode($mc, true);
        }

        if(!empty($meta['js'])){
            foreach($meta['js'] as $path){
                $js[] = Url::isRelative($path) ? $themeUrl.'/'.$path : $path;
            }
        }

        if(!empty($meta['css'])){
            foreach($meta['css'] as $path){
                $css[] = Url::isRelative($path) ? $themeUrl.'/'.$path : $path;
            }
        }

        $jsFile = $snippetPath.'/index.js';
        if(file_exists($jsFile)){
            $js[] = $snippetUrl.'/index.js';
        }

        $cssFile = $snippetPath.'/index.css';
        if(file_exists($cssFile)){
            $css[] = $snippetUrl.'/index.css';
        }

        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>iFrame</title>';
        $html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
        foreach($js as $jsFile){
            $html .= '<script src="'.$jsFile.'"></script>';
        }
        foreach($css as $cssFile){
            $html .= '<link href="'.$cssFile.'" rel="stylesheet">';
        }
        $html .= '</head>';
        $html .= '<body>';
        $html .= $content;
        $html .= '</body>';
        $publish = $am->publish(Yii::getAlias('@app/assets/iframeresizer/iframeresizer.contentwindow.min.js'));
        $html .= '<script type="text/javascript" src="'.$publish[1].'" defer></script>';
        $html .= '</html>';
        return $html;
    }

    private function getMeta($snippetPath){
        $metaFile = $snippetPath.'/meta.json';
        $content = file_get_contents($metaFile);
        $res = json_decode($content, true);
        return $res ? $res : [];
    }

}
