<?php

/* @var $model humhub\modules\documentmanager\models\ConfigureForm */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h1>Document Manager</h1>
    </div>

    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'id' => 'configure-form',
        ]);
        ?>

        <div class="form-group">
            <?= $form->field($model, 'class')->textInput();?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'driverName')->textInput();?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'dsn')->textInput();?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'username')->textInput();?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'password')->passwordInput();?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'charset')->textInput();?>
        </div>

        <?= Html::submitButton(Yii::t('base', 'Save'), ['class' => 'btn btn-primary']) ?>

    	<?php ActiveForm::end(); ?>
    </div>
</div>
