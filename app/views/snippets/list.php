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
            <div class="snippet-header clearfix">
                <div class="float-left">
                    <a class="snippet-link" href="<?= Url::toRoute(['/snippets/view', 'id' => $id]) ?>"><?= $snippet['name'] ?></a>
                    <div class="snippet-tags">
                        <? foreach($snippet['tags'] as $tag): ?>
                            <a href="<?= Url::toRoute(['/snippets/list', 'tags' => $tag])?>"><span class="label"><?= $tag ?></span></a>
                        <? endforeach; ?>
                    </div>
                </div>
                <div class="float-right">
                    <? if(!empty($snippet['date'])): ?><span class="snippet-date"><?= $snippet['date']; ?></span><? endif; ?>
                </div>
            </div>
            <iframe class="snippet-frame" src="<?= Url::toRoute(['snippets/iframe', 'id' => $id]) ?>"></iframe>
        </div>
        <? endforeach; ?>

        <?= LinkPager::widget(['pagination' => $pagination]); ?>

    </div>

</div>
<?
//$this->registerJs(" //iFrameResize({scrolling: true}); ");
?>