<?php

use yii\helpers\Html;


/** @var humhub\modules\documentmanager\models\DocumentRevision $model */

$this->title = 'Create Revision';
$this->params['breadcrumbs'][] = ['label' => 'Revisions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="revision-create">

    <h1 style="padding-left: 12px;"><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_revisionform', [
        'model' => $model,
    ]) ?>

</div>

<?php
