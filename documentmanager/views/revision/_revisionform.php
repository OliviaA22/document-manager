<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

use humhub\modules\documentmanager\assets\DocumentManagerAsset;
use humhub\modules\documentmanager\models\DocumentRevision;
use humhub\modules\documentmanager\models\Document;
use humhub\modules\documentmanager\models\Affiliation;
use yii\helpers\Url;


/** @var yii\web\View $this */
/** @var humhub\modules\documentmanager\models\DocumentRevision $model */

/** @var yii\widgets\ActiveForm $form */

?>

<div class="revision-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); // Enable file uploads 
    ?>

    <?= $form->field($model, 'fk_document')->textInput(['value' => $model->document->name, 'disabled' => true]) ?>
    
    <?= $form->field($model, 'fk_document')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'document_content')->fileInput() ?>

    <?= $form->field($model, 'fk_affiliation')->checkboxList(
        ArrayHelper::map(Affiliation::find()->all(), 'id', 'name')
    ) ?>

    <?= $form->field($model, 'version')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tags')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_visible')->dropDownList(['1' => 'Yes', '0' => 'No']) ?>

    <?= $form->field($model, 'is_informed')->dropDownList(['1' => 'Yes', '0' => 'No']) ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <a href="<?= Yii::$app->request->referrer ?>" class="btn btn-primary">Back</a>

        <?= Html::submitButton('Save', ['class' => 'btn btn-success pull-right']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>