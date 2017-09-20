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
        $snippetPath = $snippetsManager->basePath.'/'.$id;

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
            'created_at' => time()
        ]);

        $request = Yii::$app->getRequest();
        if ($snippet->load($request->post()) && $snippet->validate()) {
            yii::$app->getSession()->setFlash('success', 'Snippet created successfully.');
            return Yii::$app->getResponse()->redirect(['snippets/edit', 'id' => $snippet->id]);
        }

        return $this->render('create', [
            'snippet' => $snippet,
        ]);
    }

    public function actionEdit($id){
        $snippet = Snippet::findOne($id);
        if(!$snippet){
            throw new HttpException(404, 'Snippet does not exists.');
        }

        $request = Yii::$app->getRequest();
        if ($snippet->load($request->post()) && $snippet->validate()) {
            yii::$app->getSession()->setFlash('success', 'Snippet modified successfully.');
            return Yii::$app->getResponse()->redirect(['snippets/edit', 'id' => $id]);
        }

        return $this->render('edit', [
            'snippet' => $snippet,
        ]);
    }


    public function actionDelete($id){
        $snippet = Snippet::findOne($id);
        if(!$snippet){
            throw new HttpException(404, 'Snippet does not exists.');
        }

        $deleted = $snippet->delete() ? 1 : 0;
        if($deleted){
            yii::$app->getSession()->setFlash('success', 'Snippet deleted successfully.');
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
        }

        return $this->redirect(['/snippets/tools']);
    }

    /*
    public function actionRebuild2()
    {

        $snippetsManager = Yii::$app->getSnippets();
        $snippetsPath = $snippetsManager->basePath;

        $cachePath = Yii::$app->getRuntimePath() . '/snippets';
        if (!is_dir($cachePath)) {
            FileHelper::createDirectory($cachePath, 0775, true);
        }

        $tagIndexFile = Yii::$app->getRuntimePath().'/snippets/tags.php';
        $tagIndex = is_file($tagIndexFile) ? include($tagIndexFile) : [];

        $handle = opendir($snippetsPath);
        $tags = [];
        while (($snippetId = readdir($handle)) !== false) {
            if ($snippetId === '.' || $snippetId === '..') {
                continue;
            }

            $path = $snippetsPath . DIRECTORY_SEPARATOR . $snippetId;
            $metaFile = $path.'/snippet.json';
            if(!is_file($metaFile)){
                continue;
            }

            $content = file_get_contents($metaFile);
            if(!($meta = json_decode($content, true))){
                continue;
            }

            if(!empty($meta['tags'])){
                foreach ($meta['tags'] as $tag) {
                    $tags[$tag][] = $snippetId;
                }
            }
        }
        closedir($handle);


        $array = VarDumper::export($tags);
        $content = <<<EOD
<?php
// tag index
return $array;

EOD;

        return $this->redirect(['/snippets/tools']);
    }
    */

}
