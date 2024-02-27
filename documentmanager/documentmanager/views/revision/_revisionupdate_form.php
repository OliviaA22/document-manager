<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

use humhub\modules\documentmanager\models\Affiliation;

/** @var yii\web\View $this */
/** @var humhub\modules\documentmanager\models\DocumentRevision $model */

/** @var yii\widgets\ActiveForm $form */


?>

<div class="revision-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); // Enable file uploads 
    ?>



    <div class="form-group">
        <label class="control-label" for="content">Document</label>
        </br>
        <?= Html::a($model->document->name, ['download', 'id' => $model['id']], ['style' => 'text-decoration: none;', 'name' => 'content']);  ?>
        <!-- displays a link to the document to be updated -->
    </div>


<?= $form->field($model->document, 'affiliations')->checkboxList(
        ArrayHelper::map(Affiliation::find()->all(), 'id', 'name'),
        ['multiple' => 'multiple', array('value' => $model->document->documentAffiliations, 'uncheckValue' => '')]
    ) ?>

    <?= $form->field($model->document, 'tags')->textInput(['value' => $model->document->tags]) ?>

    <?= $form->field($model, 'version')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_visible')->dropDownList(['1' => 'Yes', '0' => 'No']) ?>

    <?= $form->field($model, 'is_informed')->dropDownList(['1' => 'Yes', '0' => 'No']) ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success pull-right']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <a href="<?= Yii::$app->request->referrer ?>" class="btn btn-primary">Back</a>
</div>