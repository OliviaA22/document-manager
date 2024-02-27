<?php

/* @var $model humhub\modules\documentmanager\models\ConfigureForm */

use humhub\modules\documentmanager\assets\DocumentManagerAsset;
use kartik\widgets\TimePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

DocumentManagerAsset::register($this);

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
            <?= $form->field($model, 'class')->textInput(); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'driverName')->textInput(); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'dsn')->textInput(); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'username')->textInput(); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'password')->passwordInput(); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'charset')->textInput(); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'strtotimeString')->hiddenInput(['value' => $model->strtotimeString])->label(false); ?>
        </div>

        <?php $days = [
            'Monday' => 'Monday',
            'Tuesday' => 'Tuesday',
            'Wednesday' => 'Wednesday',
            'Thursday' => 'Thursday',
            'Friday' => 'Friday',
            'Saturday' => 'Saturday',
            'Sunday' => 'Sunday',
        ]; ?>
        <div class="row">
            <?= Html::tag('p', 'The fields below are used to configure the CronJob schedule for automated notification.', ['class' => 'explanatory-text']);
            ?>
            <div class="col-sm-4">
                <?= $form->field($model, 'weekday')->dropDownList($days, ['prompt' => 'Select Day', ]); ?>
            </div>
            <div class="col-sm-4">
                <?= $form->field($model, 'time')->widget(TimePicker::class, [
                    'name' => 'setTime',
                    'options' => [
                    ],
                    'pluginOptions' => [
                        'showSeconds' => false,
                        'showMeridian' => false,
                        'minuteStep' => 1,
                    ],
                ]); ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('base', 'Save'), ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>