<?php

namespace app\controllers;

use Yii;

use yii\web\Controller;
use yii\base\Exception;
use yii\web\HttpException;
use yii\db\Query;
use yii\data\Pagination;
use yii\helpers\Url;
use app\models\Snippet;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;

class SnippetsController extends Controller
{

    public $defaultAction = 'list';

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $request = Yii::$app->getRequest();
            $snippetsManager = Yii::$app->getSnippets();

            if($theme = $request->get('theme')){
                $snippetsManager->setCurrentTheme($theme);
                return $this->redirect(Url::current(['theme' => null]));
            }

            return true;
        }

        return false;
    }
    
    public function actionList()
    {
        $request = Yii::$app->getRequest();
        $snippetsManager = Yii::$app->getSnippets();

        $fw = $snippetsManager->getCurrentFramework();
        $reqTags = $request->get('tags');
        $reqTags = $reqTags ? preg_split('/\s*[,;]\s*/', $reqTags, null, PREG_SPLIT_NO_EMPTY) : [];

        $cache = Yii::$app->getCache();

        $cacheKey = 'snippets.'.$fw.'.'.implode(';', $reqTags);
        $snippets = $cache->get($cacheKey);

        if($snippets === false){
            $snippets = [];
            $list = $snippetsManager->getSnippets();
            foreach($list as $id => $s){
                if(!isset($s['framework']) || $s['framework'] !== $fw){
                    continue;
                }

                if(!empty($reqTags)){
                    if(empty($s['tags'])){
                        continue;
                    }
                    foreach($reqTags as $tag){
                        if(!in_array($tag, $s['tags'])){
                            continue 2;
                        }
                    }
                }

                $snippets[$id] = $s;
            }
            $cache->set($cacheKey, $snippets, 3600);
        }

        $count = count($snippets);

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $count,
        ]);
        
        $snippets = array_slice($snippets, $pagination->getOffset(), $pagination->getLimit(), true);

        return $this->render('list', [
            'snippets' => $snippets,
            'pagination' => $pagination,
        ]);
    }
    
    public function actionView($id)
    {
        $snippetsManager = Yii::$app->getSnippets();

        $snippet = $snippetsManager->getSnippetData($id);
        if(!$snippet){
            throw new HttpException(404, 'Snippet does not exists.');
        }

        $snippetPath = $snippet['path'];

        $indexFile = $snippetPath.'/index.html';
        if(!file_exists($indexFile)){
            throw new Exception("File iframe index '$indexFile' does not exists.");
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

        return $this->render('view', [
            'snippet' => $snippet,
            'contentHtml' => $contentHtml,
            'contentJs' => $contentJs,
            'contentCss' => $contentCss,
        ]);
    }

    public function actionUpdate($id)
    {
        $request = Yii::$app->getRequest();

        $snippetsManager = Yii::$app->getSnippets();
        $snippetPath = $snippetsManager->snippetsPath.'/'.$id;

        $indexFile = $snippetPath.'/index.html';
        if(!file_exists($indexFile)){
            throw new Exception("File iframe index '$indexFile' does not exists.");
        }


        if(($html = $request->post('html')) !== null){
            file_put_contents($indexFile, $html);
        }
        elseif(($css = $request->post('css')) !== null){
            $cssFile = $snippetPath.'/index.css';
            if(trim($css) == ""){
                unlink($cssFile);
            }
            else {
                file_put_contents($cssFile, $css);
            }
        }
        elseif(($js = $request->post('js')) != null){
            $jsFile = $snippetPath.'/index.js';
            if(trim($js) == ""){
                unlink($jsFile);
            }
            else {
                file_put_contents($jsFile, $js);
            }
        }

        return $this->redirect(['/snippets/view', 'id' => $id]);
    }

    public function actionIframe($id)
    {
        return Yii::$app->getSnippets()->renderIframe($id);
    }

    public function actionManage(){
        $snippetsManager = Yii::$app->getSnippets();

        $snippets = $snippetsManager->getSnippets();

        return $this->render('manage', [
            'snippets' => $snippets,
        ]);
    }

    public function actionCreate(){
        $snippet = new Snippet([
            'date' => date('d-m-Y H:m')
        ]);

        $request = Yii::$app->getRequest();
        if($snippet->load($request->post()) && $snippet->save()){
            yii::$app->getSession()->setFlash('success', 'Snippet created successfully.');
            return Yii::$app->getResponse()->redirect(['snippets/edit', 'id' => $snippet->id]);
        }

        return $this->render('create', [
            'snippet' => $snippet,
        ]);
    }

    public function actionEdit($id){
        $snippet = Snippet::findSnippet($id);
        if(!$snippet){
            throw new HttpException(404, 'Snippet does not exists.');
        }

        $request = Yii::$app->getRequest();
        if($snippet->load($request->post()) && $snippet->save()){
            yii::$app->getSession()->setFlash('success', 'Snippet modified successfully.');
            return Yii::$app->getResponse()->redirect(['snippets/edit', 'id' => $id]);
        }

        return $this->render('edit', [
            'snippet' => $snippet,
        ]);
    }

    public function actionDelete($id){
        $snippetsManager = Yii::$app->getSnippets();
        $snippetPath = $snippetsManager->snippetsPath.'/'.$id;
        if(is_dir($snippetPath)) {
            FileHelper::removeDirectory($snippetPath);


            $cache = Yii::$app->getCache();
            if(isset($cache->cachePath)){
                FileHelper::removeDirectory($cache->cachePath);
            }

            Yii::$app->getSession()->setFlash('success', 'Snippet deleted successfully.');
        }

        return Yii::$app->getResponse()->redirect(['snippets/manage']);
    }

    public function actionTools()
    {
        return $this->render('tools', []);
    }


    public function actionClearCache()
    {
        $cache = Yii::$app->getCache();
        if(isset($cache->cachePath)){
            FileHelper::removeDirectory($cache->cachePath);
            yii::$app->getSession()->setFlash('success', 'Cache was cleared successfully.');
        }

        return $this->redirect(['/snippets/tools']);
    }

}
