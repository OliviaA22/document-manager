<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var humhub\modules\documentmanager\models\Document $model */

$this->title = 'Update Document: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Documents', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="document-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_documentform', [
        'model' => $model,
    ]) ?>

</div>
