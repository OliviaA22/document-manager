<?php

namespace humhub\modules\documentmanager\models;

use Yii;

/**
 * This is the model class for table "revision (used to combine the document and revision table)".
 *
 * @property int $id
 * @property int| $fk_document
 * @property object $document_content
 * @property string $version
 * @property int $is_visible
 * @property string $created_date
 * @property int $is_informed
 * @property string|null $comment
 *
 * @property Document $fkDocument
 * @property Folder $fk_folder
 * @property FolderHierarchy $folderdata
 */

class DocumentRevision extends Revision
{
    public $name;
    public $tags;
    public $fk_affiliation;
    public $affiliation;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fk_document' => 'Document',
            'document_content' => 'Upload File',
            'version' => 'Version',
            'fk_affiliation' => 'Affiliations',
            'is_visible' => 'Is Visible',
            'created_date' => 'Modified Date',
            'is_informed' => 'Informed?',
            'comment' => 'Comment',
        ];
    }

    public static function getVisibleRevisions($fk_folder = null)
    {
        return self::find()
            ->select('revision.id, revision.fk_document, revision.version, revision.is_visible, revision.created_date, revision.is_informed, revision.comment, document.name, document.tags')
            ->joinWith('document')
            ->where(['revision.is_visible' => 1])
            ->where(['fk_folder' => $fk_folder])
            ->all();
    }
    public static function getAllRevisions($fk_folder = null)
    {
        return self::find()
            ->select('revision.id, revision.fk_document, revision.version, revision.is_visible, revision.created_date, revision.is_informed, revision.comment, document.name, document.tags, affiliation.name AS affiliation')
            ->joinWith('document')
            ->leftJoin('affiliation_document', 'affiliation_document.fk_document = document.id')
            ->leftJoin('affiliation', 'affiliation_document.fk_affiliation = affiliation.id')
            ->where(['fk_folder' => $fk_folder])
            ->all();
    }


    public function getSearchRevisions($documentrevision)
    {

        $query = DocumentRevision::find()
            ->select('revision.id, revision.fk_document, revision.version, revision.is_visible, revision.created_date, revision.is_informed, revision.comment, document.name, document.tags, affiliation.name AS affiliation')
            ->joinWith('document')
            ->leftJoin('affiliation_document', 'affiliation_document.fk_document = document.id')
            ->leftJoin('affiliation', 'affiliation_document.fk_affiliation = affiliation.id');
   
            if ($documentrevision->search) {
                $query->andWhere(['or', ['like', 'document.name', $documentrevision->search], ['like', 'document.tags', $documentrevision->search]]);
            }

            if ($documentrevision->fk_affiliation) {

                $query->andWhere(['affiliation.id'=> $documentrevision->fk_affiliation]);
            }

        // echo "<pre>";
        // print_r($query->createCommand()->getRawSql());
        // echo "</pre>";
        // die;

        return $query->all();
    }





    /**
     * Gets query for [[Document]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocument()
    {
        return $this->hasOne(Document::class, ['id' => 'fk_document']);
    }
}


/*
$POST = [
    "val1" = "test",
    "val2" = [
        0 => "asfgjdn",
    ]
]

*/