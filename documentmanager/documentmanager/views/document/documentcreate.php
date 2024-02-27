<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var humhub\modules\documentmanager\models\Document $model */
/** @var humhub\modules\documentmanager\models\Folderhierarchy $hierarchyModel */
/** @var humhub\modules\documentmanager\models\Folder $folderModel */
/** @var humhub\modules\documentmanager\models\Revision $revisionModel */

$this->title = 'Create Document';
$this->params['breadcrumbs'][] = ['label' => 'Documents', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="document-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_documentform', [
        'model' => $model,
        'folderModel' => $folderModel,
        'revisionModel' => $revisionModel,
        'hierarchyModel' => $hierarchyModel,
        ]) ?>

</div>
