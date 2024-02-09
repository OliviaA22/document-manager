<?php

namespace humhub\modules\documentmanager\models;

use humhub\modules\documentmanager\components\ExternalDatabase;
use Yii;

/**
 * This is the model class for table "affiliation_document".
 *
 * @property int $id
 * @property int $fk_document
 * @property int $fk_affiliation
 *
 * @property Affiliation $fkAffiliation
 * @property Document $fkDocument
 */
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
