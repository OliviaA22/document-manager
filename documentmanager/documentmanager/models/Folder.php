<?php

namespace humhub\modules\documentmanager\models;

use humhub\modules\documentmanager\controllers\BackendController;
use humhub\modules\documentmanager\helpers\DocumentManagerHelper;
use Yii;
use yii\widgets\Breadcrumbs;

/**
 * This is the model class for table "folder".
 *
 * @property int $id
 * @property int|null $fk_folder
 * @property string $name
 * @property int $root_folder
 * @property int|null $sub_level
 * @property string $created_date
 *
 * @property Document[] $documents
 * @property Folder $fkFolder
 * @property Folder[] $folders
 */
class Folder extends ActiveRecordExternal
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'folder';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fk_folder', 'root_folder', 'sub_level'], 'integer'],
            [['name'], 'required'],
            [['name'], 'string'],
            [['created_date'], 'safe'],
            [['fk_folder'], 'exist', 'skipOnError' => true, 'targetClass' => Folder::class, 'targetAttribute' => ['fk_folder' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fk_folder' => 'Folder Name',
            'name' => 'Name',
            'root_folder' => 'Root Folder',
            'sub_level' => 'Sub Level',
            'created_date' => 'Modified Date',
        ];
    }


    /**
     * Creates a new folder instance using the FolderHierarchy model.
     * 
     * @param FolderHierarchy $hierarchyModel
     * @return self
     */
    public static function createFromHierarchy(FolderHierarchy $hierarchyModel)
    {
        $folder = new self();
        $folder->name = $hierarchyModel->name;
        $folder->fk_folder = $hierarchyModel->fk_folder;
        $folder->created_date = date('Y-m-d H:i:s');
        $folder->root_folder = 0;

        return $folder;
    }

    /**
     * Generates a breadcrumb trail for a given folder.
     * 
     * @param int $fk_folder
     * @return array 
     */
    public static function getBreadcrumbs($fk_folder)
    {

        $currentFolder = Folder::find()->where(['id' => $fk_folder])->one();

        while (!empty($currentFolder->fk_folder)) {

            $folderValues[] = $currentFolder;
            // Move up to the next level
            $currentFolder = Folder::find()->where(['id' => $currentFolder->fk_folder])->one();
        }
        if (empty($currentFolder->fk_folder)) {

            $folderValues[] = $currentFolder;
        }

        return $folderValues;
    }

    /**
     * Initializes a Breadcrumbs object with the breadcrumb trail for the current folder.
     * 
     * @param bool $is_backend
     * @return Breadcrumbs
     */
    public static function setBreadcrumbsPath($is_backend)
    {
        $path = new Breadcrumbs();
        $crumbsurl = Folder::getContentSettings($is_backend);
        $path->homeLink = [
            'label' => Yii::t('DocumentmanagerModule.app', 'Document Manager'),
            'url' => [
                $crumbsurl,
                'cguid' => DocumentManagerHelper::getCGuid()
            ]
        ];
        $fk_folder = Yii::$app->getRequest()->getQueryParam('fk_folder');

        $folderValues = [];

        if ($fk_folder !== null) {

            $folderValues = Folder::getBreadcrumbs($fk_folder);

            foreach ($folderValues as $folder) {
                $label = $folder->name;
                $path->links[] = [
                    'label' => $label,
                    'url' => [
                        $crumbsurl,
                        'cguid' => DocumentManagerHelper::getCGuid(),
                        'fk_folder' => $folder->id
                    ]
                ];
            }
            // Reverse the order to display the breadcrumbs from parent to child
            $path->links = array_reverse($path->links);
        }
        return $path;
    }

    /**
     * Returns the URL for the content container settings (backend or frontend).
     * 
     * @param bool $is_backend
     * @return string The URL
     */
    public static function getContentSettings($is_backend)
    {

        if ($is_backend) {
            $crumbsurl = '/documentmanager/backend/get-contents-admin';
        } else {
            $crumbsurl = '/documentmanager/frontend/get-contents';
        }

        return $crumbsurl;
    }


    /**
     * Gets query for [[Documents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['fk_folder' => 'id']);
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
     * Gets query for [[Folders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFolders()
    {
        return $this->hasMany(Folder::class, ['fk_folder' => 'id']);
    }
}
