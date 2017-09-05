<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Tools';


?>


<div id="page_content">
    <h1><?= Html::encode($this->title) ?></h1>

    <a class="btn btn-primary" href="<?= Url::toRoute(['/snippets/rebuild'])?>">Rebuild Index</a>

    <a class="btn btn-primary" href="<?= Url::toRoute(['/snippets/clear-cache'])?>">Clear Cache</a>
</div>

<?

?>

