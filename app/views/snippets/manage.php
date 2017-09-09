<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Snippet;

$this->title = 'Manage Snippets';

?>

<div id="page_content">

    <div class="buttons" style="float: right;">
        <a class="btn btn-success" href="<?= Url::toRoute(['/snippets/create']) ?>"><span class="icon icon-plus" aria-hidden="true"></span> Criar Snippet</a>
    </div>
    <h1>Snippets</h1>

    <? if($snippets): ?>
        <table class="datalist">
            <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Data</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <? foreach($snippets as $snippet): ?>
            <?
                $url = Url::toRoute(['/snippets/edit', 'id' => $snippet->id]);
            ?>
            <tr>
                <td><a href="<?= Url::toRoute(['/snippets/view', 'id' => $snippet->id]) ?>"><?= $snippet->id ?></a></td>
                <td><?= Html::encode($snippet->name); ?></td>
                <td><?= $snippet->created_at ? date('d/m/Y', $snippet->created_at) : ''; ?></td>
                <td class="actions">
                    <a class="btn btn-sm btn-edit" title="Edit" href="<?= $url; ?>">edit</a>
                    <a class="btn btn-sm btn-remove" href="<?= Url::toRoute(['/snippets/delete', 'id' => $snippet->id]) ?>" onclick="return confirm('Are you sure you want to delete this item?');"><span aria-hidden="true">&times;</span></a>
                </td>
            </tr>
            <? endforeach; ?>
            </tbody>
        </table>
    <? else: ?>
        <p class="no-items">
            Ainda não adicionou nenhum snippet. <a class="btn btn-secondary" href="<?= Url::toRoute(['/snippets/create']); ?>">Criar snippet</a>
        </p>
    <? endif; ?>
</div>