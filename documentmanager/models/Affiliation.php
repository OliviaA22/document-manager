<?php

namespace humhub\modules\documentmanager\models;

use Yii;

/**
 * This is the model class for table "affiliation".
 *
 * @property int $id
 * @property string|null $name
 *
 * @property AffiliationDocument[] $affiliationDocuments
 */
class Affiliation extends ActiveRecordExternal
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'affiliation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * Gets query for [[AffiliationDocuments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAffiliationDocuments()
    {
        return $this->hasMany(AffiliationDocument::class, ['fk_affiliation' => 'id']);
    }
}
