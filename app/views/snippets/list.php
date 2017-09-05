<?php
use yii\helpers\Url;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */

$this->title = 'Snippets List';
?>

<div class="page-snippets-list">

    <div id="page_content">

        <h1>Snippets
            <? if($tag = Yii::$app->request->get('tag')): ?>
            Tag "<?= $tag ?>"
            <? endif;?>
        </h1>

        <? foreach($snippets as $id => $snippet): ?>
        <div class="snippet-item">
            <div class="snippet-header">
                <a class="snippet-link" href="<?= Url::toRoute(['snippets/view', 'id' => $id]) ?>"><?= $snippet['name'] ?></a>
                <div class="snippet-tags">
                    <? foreach($snippet['tags'] as $tag): ?>
                        <a href="<?= Url::toRoute(['snippets/list', 'tag' => $tag['tag_id']])?>"><span class="label"><?= $tag['tag_id'] ?></span></a>
                    <? endforeach; ?>
                </div>
            </div>
            <iframe class="snippet-frame" style="max-height: 300px;" id="tpl_<?= $id ?>" src="<?= Url::toRoute(['snippets/iframe', 'id' => $id]) ?>"></iframe>
        </div>
        <? endforeach; ?>

        <?= LinkPager::widget(['pagination' => $pagination]); ?>

    </div>

</div>
<?
$this->registerJs("
    iFrameResize({scrolling: true});
");
?>