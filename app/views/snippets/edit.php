<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Edit Snippet #'.$snippet->id;

?>


<div id="page_content">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <? $form = ActiveForm::begin(); ?>

    <?= $form->field($snippet, 'id')->textInput(['disabled' => true]) ?>
    <?= $form->field($snippet, 'name') ?>
    <?= $form->field($snippet, 'tags')->textInput(['placeholder' => 'tag1, tags2']) ?>
    <?= $form->field($snippet, 'createdAtText')->textInput(['placeholder' => date('d-m-Y')]) ?>
    <?= $form->field($snippet, 'framework')->dropDownList($snippet->frameworkOptions()); ?>
    <?= $form->field($snippet, 'css')->checkbox() ?>
    <?= $form->field($snippet, 'js')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
        <a href="<?= Url::toRoute(['snippets/view', 'id' => $snippet->id]) ?>">preview</a>
    </div>
    
    <? ActiveForm::end(); ?>
</div>