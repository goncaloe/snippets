<?php
use yii\helpers\Url;
/* @var $this yii\web\View */

$this->title = 'Tags';


?>


<div id="page_content">


    <ul>
    <? foreach($tags as $tag): ?>
        <li><a href="<?= Url::toRoute(['snippets/list', 'tag' => $tag['id']]) ?>"><?= $tag['id'].' ('.$tag['count'].')'; ?></a></li>
    <? endforeach; ?>
    </ul>

</div>