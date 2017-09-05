<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Create Snippet';


?>


<div id="page_content">
    <h1><?= Html::encode($this->title) ?></h1>

    <? $form = ActiveForm::begin(['id' => 'contact-form']); ?>

    <?= $form->field($snippet, 'id') ?>
    <?= $form->field($snippet, 'name') ?>

    <button type="submit" class="btn btn-primary">Criar</button>
    <?php ActiveForm::end(); ?>
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

