<?php
use yii\helpers\Url;
/* @var $this yii\web\View */

$this->title = 'Tags';


?>


<div id="page_content">
    <h1>Tags</h1>

    <table class="datalist">
        <thead>
        <tr>
            <th>Tag</th>
            <th>Count</th>
        </tr>
        </thead>
        <tbody>
        <? foreach($tags as $tag): ?>
            <tr>
                <td><a href="<?= Url::toRoute(['snippets/list', 'tag' => $tag['id']]) ?>"><?= $tag['id']; ?></a></td>
                <td><?= $tag['count']; ?></a></td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>

</div>