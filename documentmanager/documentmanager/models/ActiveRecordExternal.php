<?php

namespace humhub\modules\documentmanager\models;

use humhub\modules\documentmanager\components\ExternalDatabase;


class ActiveRecordExternal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function getDb()
    {
        return ExternalDatabase::getConnection();
    }
}
