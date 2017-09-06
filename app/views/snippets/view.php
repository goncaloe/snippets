<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\CodemirrorAsset;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */

$this->title = 'Snippet '.$snippet['name'];

CodemirrorAsset::register($this);

?>

<div class="page-snippets-view">

    <div id="page_content">
        <h1>Snippet <?= $snippet['name'] ?></h1>

        <div class="snippet-tags">
            <? foreach($tags as $tag): ?>
            <a href="<?= Url::toRoute(['snippets/list', 'tag' => $tag['tag_id']])?>"><span class="label"><?= $tag['tag_id'] ?></span></a>
            <? endforeach; ?>
        </div>

        <ul class="snippet-buttons">
            <li><a class="tab-link" href="#iframe_content" id="show-preview">Preview</a></li>
            <li><a class="tab-link" href="#html_content" id="show-html">HTML</a></li>
            <? if($contentCss): ?><li><a class="tab-link" href="#css_content" id="show-css">CSS</a></li><? endif; ?>
            <? if($contentJs): ?><li><a class="tab-link" href="#js_content" id="show-js">JS</a></li><? endif; ?>
            <li><a href="<?= Url::toRoute(['snippets/iframe', 'id' => $snippet['id']]) ?>" target="_blank" id="open_iframe">IFRAME</a></li>
        </ul>
    </div>

    <div id="editors_content">
        <div class="tab-content" id="html_content">
            <? $form = ActiveForm::begin(['action' => ['/snippets/update', 'id' => $snippet['id']]]) ?>
                <textarea class="form-editor" id="html_editor" name="html" style="display:none;"><?= Html::encode($contentHtml) ?></textarea>
                <button type="submit" class="btn btn-primary">Guardar</button>
            <? ActiveForm::end(); ?>
        </div>
        <? if($contentCss): ?>
            <div class="tab-content" id="css_content">
                <? $form = ActiveForm::begin(['action' => ['/snippets/update', 'id' => $snippet['id']]]) ?>
                    <textarea class="form-editor" id="css_editor" name="css" style="display:none;"><?= Html::encode($contentCss) ?></textarea>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                <? ActiveForm::end(); ?>
            </div>
        <? endif; ?>
        <? if($contentJs): ?>
            <div class="tab-content" id="js_content">
                <? $form = ActiveForm::begin(['action' => ['/snippets/update', 'id' => $snippet['id']]]) ?>
                    <textarea class="form-editor" id="js_editor" name="js" style="display:none;"><?= Html::encode($contentJs) ?></textarea>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                <? ActiveForm::end(); ?>
            </div>
        <? endif; ?>
    </div>

    <div class="tab-content" id="iframe_content">
        <iframe class="snippet-frame" id="tpl_<?= $snippet['id'] ?>" src="<?= Url::toRoute(['snippets/iframe', 'id' => $snippet['id']]) ?>"></iframe>
    </div>
</div>
<?
$js = <<<JS

var buttons = $('.snippet-buttons a.tab-link');
buttons.on('click', function(e){
    e.preventDefault();
    var current = $(this);
    var ctarget = current.attr('href');
    console.log(ctarget);
    buttons.parent().removeClass('active');
    current.parent().addClass('active');
    buttons.each(function(){
        var target = $(this).attr('href');
        if(target == ctarget){
            $(target).show();
        }
        else {
            $(target).hide();
        }
    });

    return false;
});

CodeMirror.fromTextArea(document.getElementById("html_editor"), {
    lineNumbers: true,
    viewportMargin: Infinity,
    mode: {
        "name": "htmlmixed"
    }
});
JS;

if($contentCss){
    $js .= <<<JS
    CodeMirror.fromTextArea(document.getElementById("css_editor"), {
        lineNumbers: true,
        mode: "text/css"
    });
JS;
}

if($contentJs){
    $js .= <<<JS
    CodeMirror.fromTextArea(document.getElementById("js_editor"), {
        lineNumbers: true,
        mode: "text/javascript"
    });
JS;
}

$js .= " buttons.first().trigger('click');";

$this->registerJs($js);

$this->registerJs("iFrameResize({scrolling: true});", \yii\web\View::POS_END);
?>