<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;


use humhub\modules\documentmanager\models\FolderHierarchy;
use humhub\modules\documentmanager\models\Folder;
use humhub\modules\documentmanager\models\Revision;
use humhub\modules\documentmanager\models\Document;
use humhub\modules\documentmanager\models\Affiliation;

use humhub\modules\documentmanager\assets\DocumentManagerAsset;

DocumentManagerAsset::register($this);

/** @var yii\web\View $this */
/** @var humhub\modules\documentmanager\models\Document $model */
/** @var humhub\modules\documentmanager\models\Folderhierarchy $hierarchyModel */
/** @var humhub\modules\documentmanager\models\Folder $folderModel */
/** @var humhub\modules\documentmanager\models\Revision $revisionModel */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="document-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($hierarchyModel, 'fk_folder')->dropDownList(
        FolderHierarchy::getDropdownData(),
        ['prompt' => 'Select Folder', 'id' => 'folder-field', 'encodeSpaces' => true,]
    ) ?>


    <div class="form-group">
        <label for="create-new-folder-checkbox">
            <input type="checkbox" id="create-new-folder-checkbox" name="create-new-folder-checkbox"> Create New Folder
        </label>
    </div>
    <?= $form->field($hierarchyModel, 'fk_folder')->dropDownList(
        FolderHierarchy::getDropdownData(),
        ['prompt' => 'Select Root Folder', 'id' => 'parent-folder-field', 'encodeSpaces' => true, 'disabled' => true]
    ) ?>

    <?= $form->field($hierarchyModel, 'name')->textInput(['maxlength' => true, 'id' => 'new-folder-fields', 'disabled' => true]) ?>

    <?= $form->field($revisionModel, 'document_content')->fileInput() ?>

    <?= $form->field($model, 'tags')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'affiliations')->checkboxList(
        ArrayHelper::map(Affiliation::find()->all(), 'id', 'name')
    ) ?>

    <?= $form->field($revisionModel, 'version')->textInput(['maxlength' => true]) ?>

    <?= $form->field($revisionModel, 'is_visible')->dropDownList(['1' => 'Yes', '0' => 'No']) ?>

    <?= $form->field($revisionModel, 'is_informed')->dropDownList(['1' => 'Yes', '0' => 'No']) ?>
    
    <?= $form->field($revisionModel, 'comment')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <a href="<?= Yii::$app->request->referrer ?>" class="btn btn-primary">Back</a>

        <?= Html::submitButton('Save', ['class' => 'btn btn-success pull-right']) ?>

    </div>
    <?php ActiveForm::end(); ?>
</div>