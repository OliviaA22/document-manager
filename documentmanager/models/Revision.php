<?php

namespace humhub\modules\documentmanager\models;

use Yii;

/**
 * This is the model class for table "revision".
 *
 * @property int $id
 * @property int $fk_document
 * @property object $document_content
 * @property string $version
 * @property int $is_visible
 * @property string $created_date
 * @property int $is_informed
 * @property string|null $comment
 *
 * @property Document $fkDocument
 */
class Revision extends ActiveRecordExternal
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'revision';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fk_document', 'is_visible', 'is_informed'], 'integer'],
            [['document_content', 'comment'], 'string'],
            [['version', 'is_visible', 'is_informed'], 'required'],
            [['created_date'], 'safe'],
            [['version'], 'string', 'max' => 50],
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
            'document_content' => 'Document Content',
            'version' => 'Version',
            'is_visible' => 'Is Visible',
            'created_date' => 'Modified Date',
            'is_informed' => 'Is Informed',
            'comment' => 'Comment',
        ];
    }


    /**
     * Gets query for [[FkDocument]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocument()
    {
        return $this->hasOne(Document::class, ['id' => 'fk_document']);
    }
}
