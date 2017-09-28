<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Create Snippet';


?>


<div id="page_content">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <? $form = ActiveForm::begin(); ?>

    <?= $form->field($snippet, 'id') ?>
    <?= $form->field($snippet, 'name') ?>
    <?= $form->field($snippet, 'tags')->textInput(['placeholder' => 'tag1, tags2']) ?>
    <?= $form->field($snippet, 'date')->textInput(['placeholder' => date('d-m-Y H:m')]) ?>
    <?= $form->field($snippet, 'framework')->dropDownList($snippet->frameworkOptions()); ?>
    <?= $form->field($snippet, 'inc_css')->checkbox() ?>
    <?= $form->field($snippet, 'inc_js')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
    </div>
    
    <? ActiveForm::end(); ?>
</div>

<?

$inputId = Html::getInputId($snippet, 'id');
$this->registerJs("
var text = '';
var possible = 'abcdefghijklmnopqrstuvwxyz0123456789';
for (var i = 0; i < 5; i++){
    text += possible.charAt(Math.floor(Math.random() * possible.length));
}
$('#{$inputId}').val(text);
");
?>

