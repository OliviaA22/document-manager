<?php
use humhub\modules\documentmanager\assets\DocumentManagerAsset;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var humhub\modules\documentmanager\models\Revision $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Revisions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
DocumentManagerAsset::register($this->getView());
?>
<div class="revision-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'fk_document',
            'document_content',
            'version',
            'is_visible',
            'created_date',
            'is_informed',
            'comment',
        ],
    ]) ?>

</div>
