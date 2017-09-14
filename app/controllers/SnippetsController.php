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
use app\models\Tag;

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
        $snippetsManager = Yii::$app->getSnippets();

        if($theme = $request->get('theme')){
            $snippetsManager->setCurrentTheme($theme);
            return $this->redirect(Url::current(['theme' => null]));
        }

        $query = new Query();
        $query->from('snippets s');

        if($tag = $request->get('tag')){
            $query->innerJoin('snippet_tags t', 't.snippet_id = s.id AND t.tag_id = :tag', [':tag' => $tag]);
        }

        $fw = $snippetsManager->getCurrentFramework();
        $query->where(['s.framework' => $fw]);

        $count = $query->count();

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $count,
        ]);

        $snippets = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('created_at DESC')
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

        $snippetsManager = Yii::$app->getSnippets();

        $snippetPath = $snippetsManager->basePath.'/'.$id;

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

    public function actionManage(){
        $snippets = Snippet::find()
            ->orderBy('created_at DESC')
            ->all();

        // save items
        $request = Yii::$app->getRequest();
        if (Snippet::loadMultiple($snippets, $request->post()) && Snippet::validateMultiple($snippets)) {
            foreach ($snippets as $snippet) {
                $snippet->save(false, ['position']);
            }
            yii::$app->getSession()->setFlash('success', 'Snippets modified successfully.');
            return $this->refresh();
        }

        return $this->render('manage', [
            'snippets' => $snippets,
        ]);
    }

    public function actionCreate(){
        $snippet = new Snippet([
            'created_at' => time()
        ]);

        $request = Yii::$app->getRequest();
        if ($snippet->load($request->post()) && $snippet->save()) {
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
        if ($snippet->load($request->post()) && $snippet->save()) {
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
        return $this->render('tools', [
        ]);
    }


    public function actionRebuild()
    {
        $db = Yii::$app->getDb();

        $dbSnippets = (new Query())
            ->from('snippets s')
            ->indexBy('id')
            ->all($db);
        $snippetsManager = Yii::$app->getSnippets();
        $snippetsPath = $snippetsManager->basePath;

        $handle = opendir($snippetsPath);
        $foundSnippets = [];
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

            $foundSnippets[] = $snippetId;
            $metaTags = !empty($meta['tags']) ? $meta['tags'] : [];

            //insert tags
            $nextPos = (new Query())
                ->from('tags')
                ->max('position', $db) + 1;
            foreach ($metaTags as $tag) {
                $hasTag = (new Query())
                    ->from('tags')
                    ->where(['id' => $tag])
                    ->exists($db);
                if (!$hasTag) {
                    $db->createCommand()->insert('tags', [
                        'id' => $tag,
                        'position' => $nextPos++,
                    ])->execute();
                }
            }

            if(isset($dbSnippets[$snippetId])){
                $db->createCommand()->update('snippets', [
                    'id' => $snippetId,
                    'name' => isset($meta['name']) ? $meta['name'] : ucfirst($snippetId),
                    'framework' => isset($meta['framework']) ? $meta['framework'] : 'bs3',
                    'created_at' => isset($meta['date']) ? strtotime($meta['date']) : time(),
                ], ['id' => $snippetId])->execute();

                $sTags = (new Query())
                    ->select(['tag_id'])
                    ->from('snippet_tags')
                    ->where(['snippet_id' => $snippetId])
                    ->column($db);

                foreach($metaTags as $tag) {
                    if (!in_array($tag, $sTags)) {
                        $db->createCommand()->insert('snippet_tags', [
                            'snippet_id' => $snippetId,
                            'tag_id' => $tag,
                        ])->execute();
                    }
                }

                foreach($sTags as $tag) {
                    if (!in_array($tag, $metaTags)) {
                        $db->createCommand()->delete('snippet_tags', [
                            'snippet_id' => $snippetId,
                            'tag_id' => $tag,
                        ])->execute();
                    }
                }
            }
            else {
                $db->createCommand()->insert('snippets', [
                    'id' => $snippetId,
                    'name' => isset($meta['name']) ? $meta['name'] : ucfirst($snippetId),
                    'framework' => isset($meta['framework']) ? $meta['framework'] : 'bs3',
                    'created_at' => isset($meta['date']) ? strtotime($meta['date']) : time(),
                ])->execute();

                foreach($metaTags as $tag){
                    $db->createCommand()->insert('snippet_tags', [
                        'snippet_id' => $snippetId,
                        'tag_id' => $tag,
                    ])->execute();
                }
            }
        }
        closedir($handle);

        foreach($dbSnippets as $snippet){
            $snippetId = $snippet['id'];
            if (!in_array($snippetId, $foundSnippets)) {
                $db->createCommand()->delete('snippets', [
                    'id' => $snippetId,
                ])->execute();

                $db->createCommand()->delete('snippet_tags', [
                    'snippet_id' => $snippetId,
                ])->execute();
            }
        }

        return $this->redirect(['/snippets/tools']);
    }

}
