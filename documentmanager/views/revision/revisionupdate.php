<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var humhub\modules\documentmanager\models\Revision $model */

$this->title = 'Update Revision';
$this->params['breadcrumbs'][] = ['label' => 'Revisions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="revision-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_revisionupdate_form', [
        'model' => $model,
    ]) ?>

</div>
