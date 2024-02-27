<?php

namespace humhub\modules\documentmanager\models;

use Yii;

/**
 * This is the model class for table "document".
 *
 * @property int $id
 * @property int $fk_folder
 * @property string $name
 * @property string $tags
 * @property int|null $sub_level
 *
 * @property Affiliation[] $affiliations  
 * @property AffiliationDocument[] $affiliationDocuments
 * @property Folder $fk_folder
 * @property Revision[] $revisions
 */
class Document extends ActiveRecordExternal
{
    public $documentAffiliations;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fk_folder', 'name', 'tags'], 'required'],
            [['fk_folder', 'sub_level'], 'integer'],
            [['name'], 'string'],
            [['tags'], 'string', 'max' => 50],
            [['fk_folder'], 'exist', 'skipOnError' => true, 'targetClass' => Folder::class, 'targetAttribute' => ['fk_folder' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Root Folder',
            'fk_folder' => 'Parent Folder',
            'name' => 'Document Name',
            'version' => 'Version',
            'tags' => 'Tags',
            'sub_level' => 'Sub Level',
            'fk_affiliation' => 'Affiliation',
            'is_visible' => 'Is Visible',
            'created_date' => 'Modified Date',
            'is_informed' => 'Informed?',
        ];
    }

    /**
     * Retrieves all visible document revisions for search functionality .
     * 
     * @return DocumentRevision[] An array of DocumentRevision models that are visible.
     */
    public function getVisibleRevisions()
    {
        $documentRevision = new DocumentRevision();
        $revisions = $documentRevision->getSearchRevisions($this)
            ->andWhere(['revision.is_visible' => 1])
            ->all();
        return $revisions;
    }

    /**
     * Fetches all revisions associated with the current document.
     * 
     * @return Revision[]
     */
    public function checkRevision()
    {
        $revisions = Revision::find()->where(['fk_document' => $this->id])->all();
        return $revisions;
    }
    /**
     * Gets query for [[AffiliationDocuments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAffiliationDocuments()
    {
        return $this->hasMany(AffiliationDocument::class, ['fk_document' => 'id']);
    }

    /**
     * Gets query for [[Affiliations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAffiliations()
    {
        return $this->hasMany(Affiliation::class, ['id' => 'fk_affiliation'])
            ->viaTable('affiliation_document', ['fk_document' => 'id']);
    }

    /**
     * Gets query for [[FkFolder]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFkFolder()
    {
        return $this->hasOne(Folder::class, ['id' => 'fk_folder']);
    }

    /**
     * Gets query for [[Revisions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRevisions()
    {
        return $this->hasMany(Revision::class, ['fk_document' => 'id']);
    }
}
