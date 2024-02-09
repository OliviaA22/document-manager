<?php

namespace humhub\modules\documentmanager\models;

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
class AffiliationDocument extends ActiveRecordExternal
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'affiliation_document';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fk_document', 'fk_affiliation'], 'required'],
            [['fk_document', 'fk_affiliation'], 'integer'],
            [['fk_affiliation'], 'exist', 'skipOnError' => true, 'targetClass' => Affiliation::class, 'targetAttribute' => ['fk_affiliation' => 'id']],
            [['fk_document'], 'exist', 'skipOnError' => true, 'targetClass' => Document::class, 'targetAttribute' => ['fk_document' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fk_document' => 'Document',
            'fk_affiliation' => 'Affiliation',
        ];
    }

    /**
     * Gets query for [[FkAffiliation]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFkAffiliation()
    {
        return $this->hasOne(Affiliation::class, ['id' => 'fk_affiliation']);
    }

    /**
     * Gets query for [[FkDocument]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFkDocument()
    {
        return $this->hasOne(Document::class, ['id' => 'fk_document']);
    }
}
