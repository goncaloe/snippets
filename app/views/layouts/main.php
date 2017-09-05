<?php
use yii\helpers\Url;
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

    <nav id="body_header">
        <!-- Logo -->
        <a class="navbar-logo" href="<?= Yii::$app->getHomeUrl() ?>">
            Snippets
        </a>

        <ul class="navbar-links" id="navbar_site">
            <li>
                <a class="btn btn-default" href="<?= Url::to(['snippets/list'])?>">Snippets</a>
            </li>
            <li>
                <a class="btn btn-default" href="<?= Url::to(['snippets/tags'])?>">Tags</a>
            </li>
            <!--
            <li>
                <a class="btn btn-default" href="<?= Url::to(['snippets/create'])?>">Create Snippet</a>
            </li>
            -->
            <li>
                <a class="btn btn-default" href="<?= Url::to(['snippets/tools'])?>">Tools</a>
            </li>
        </ul>

        <? /*
        <ul class="navbar-links" id="navbar_account">
            <? if (Yii::$app->user->isGuest): ?>
                <li>
                    <a class="btn btn-login" href="<?= Url::to(['/site/login'])?>">Login</a>
                </li>
            <? else: ?>
                <li>
                    <?= Html::beginForm(['/site/logout'], 'post') ?>
                    <button class="btn btn-logout" type="submit">Logout <?= '(' . Yii::$app->user->identity->username . ')' ?></button>
                    <?= Html::endForm() ?>
                </li>
            <? endif; ?>
        </ul>
        */ ?>
    </nav>

    <div id="body_content">
        <aside id="main_sidebar">

            <?= app\widgets\ThemeSelect::widget(); ?>

            <?= app\widgets\TagsList::widget(); ?>

        </aside>

        <article id="main_content">
            <?= $content ?>
        </article>
    </div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
