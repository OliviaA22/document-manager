<?php

/* @var $model humhub\modules\documentmanager\models\SettingsForm */
use humhub\widgets\Button;
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
            'id' => 'settings-form',
        ]);
        ?>

        <div class="form-group">
            <?= $form->field($model, 'displayEvents')->checkbox() ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'displayEventsAdmin')->checkbox() ?>
        </div>
<?= Button::save()->submit() ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
