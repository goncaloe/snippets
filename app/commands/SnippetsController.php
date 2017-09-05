<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\db\Query;

class SnippetsController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     */
    public function actionIndex()
    {

        $db = Yii::$app->getDb();

        $dbSnippets = (new Query())
            ->from('snippets s')
            ->indexBy('id')
            ->all($db);

        $snippetPath = Yii::getAlias('@var/snippets');
        $handle = opendir($snippetPath);

        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $snippetPath . DIRECTORY_SEPARATOR . $file;
            $metaFile = $path.'/meta.json';
            if(!is_file($metaFile)){
                continue;
            }

            $content = file_get_contents($metaFile);
            if(!($meta = json_decode($content, true))){
                continue;
            }
            
            $snippetId = $file;
            $metaTags = !empty($meta['tags']) ? $meta['tags'] : [];

            //insert tags
            foreach ($metaTags as $tag) {
                $hasTag = (new Query())
                    ->from('tags')
                    ->where(['id' => $tag])
                    ->exists($db);
                if (!$hasTag) {
                    $db->createCommand()->insert('tags', [
                        'id' => $tag,
                    ])->execute();
                }
            }

            if(isset($dbSnippets[$snippetId])){
                $db->createCommand()->update('snippets', [
                    'id' => $snippetId,
                    'name' => isset($meta['name']) ? $meta['name'] : ucfirst($snippetId),
                    'fw' => isset($meta['framework']) ? $meta['framework'] : 'bs3',
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

        echo "Snippets syncronized!\n";
    }
}
