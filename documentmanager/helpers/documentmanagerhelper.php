<?php

namespace humhub\modules\documentmanager\helpers;

use humhub\modules\content\models\ContentContainerModuleState;


use humhub\modules\space\models\Space;
use humhub\modules\documentmanager\notifications\DocumentNotification;
use Yii;

class DocumentManagerHelper
{
    public static function getCGuid()
    {
        return Yii::$app->getRequest()->getQueryParam('cguid');
    }



}
