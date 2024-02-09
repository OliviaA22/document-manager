<?php

namespace humhub\modules\documentmanager\models;

use Yii;
use yii\web\ServerErrorHttpException;
use yii\helpers\ArrayHelper;
use yii\db\ActiveQuery;
/**
 * This is the model class for table "vw_folder_hierarchy".
 *
 * @property int|null $id
 * @property int|null $fk_folder
 * @property string| $name
 * @property int|null $sub_level
 */
class FolderHierarchy extends ActiveRecordExternal

{
    private static $cachedRecursiveFolder = null;
    private static $indexValue = 'value';
    private static $indexChildren = 'children';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vw_folder_hierarchy';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'fk_folder', 'sub_level'], 'integer'],
            [['name'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fk_folder' => 'Parent Folder',
            'name' => 'Folder Name',
            'sub_level' => 'Sub Level',
        ];
    }


    /**
     * Gets folders in array which shows the complete hierarchy. Example:<br/>
     * <code>[
     *     1 => 'Some folder',
     *     4 => '        Some folder in Italy', // has visually 'Some folder' as parent
     *     2 => 'Some other folder',
     *     3 => 'Yet another folder',
     *     ...
     * ]</code>
     *
     * @return array
     * @throws ServerErrorHttpException if the folders database tree has a loop
     */
    public static function getDropdownData()
    {
        
        $folderData = [];
        self::processFolderArray($folderData, static::getRecursiveData());

        return $folderData;
    }



    /**
     * Gets folders in array which shows the complete hierarchy. Example:<br/>
     * <code>[
     *     1 => [
     *         'value' => 'Some folder',
     *         'children' => [
     *             4 => [
     *                 'value' => 'Some folder in Italy',
     *             ],
     *         ],
     *     ],
     *     2 => [
     *         'value' => 'Some other folder',
     *     ],
     *     3 => [
     *         'value' => 'Yet another comfolderpany',
     *     ],
     *     ...
     * ]</code>
     *
     * @param bool $requery whether to force reloading of data from database.
     * @return array
     * @throws ServerErrorHttpException
     */

    public static function getRecursiveData(bool $requery = false)
    {
        // Check whether its required to load data from database
        if ($requery || static::$cachedRecursiveFolder === null) {

            // Get ordered folders
            $activeQuery = new ActiveQuery(FolderHierarchy::class);
            $activeQuery->select([
                'id',
                'fk_folder', 
                'name',
                'sub_level',
            ]);
                $activeQuery->from('vw_folder_hierarchy');
          

            // echo $activeQuery->createCommand()->getRawSql();
            // die();

            // Build tree
            static::$cachedRecursiveFolder = [];

            foreach ($activeQuery->each() as $folder) {
                self::addFolderRecursively(static::$cachedRecursiveFolder, $folder);
            }
        }

        return static::$cachedRecursiveFolder;
    }


    /**
     * Adds <code>$folder</code> to the proper place in <code>$data</code>.
     *
     * @param array $data the array where $folderData should be added.
     * @param FolderHierarchy $folderData the folder details that need to be added.
     * @param bool $isRoot whether to allow to add root folders at the current iteration.
     * @return bool whether adding was successful (used for recursion).
     * @throws ServerErrorHttpException
     */



     private static function addFolderRecursively(array &$data, self $folderData, bool $isRoot = true): bool
{
    // Check if folder has a parent at all
    if ($folderData['fk_folder'] === null) {
        $data[$folderData['id']][static::$indexValue] = $folderData['name'];
        return true;
    }

    // Check at the current depth level if found
    if (isset($data[$folderData['fk_folder']])) {
        $data[$folderData['fk_folder']][static::$indexChildren][$folderData['id']][static::$indexValue] = $folderData['name'];
        return true;
    }

    // Search deeper
    foreach ($data as &$details) {
        if (isset($details[static::$indexChildren])) {
            if (self::addFolderRecursively($details[static::$indexChildren], $folderData, false)) {
                return true;
            }
        }
    }
        /* Went through all children recursively and didn't find the proper place to add
         * => tell back recursively that the folder was not added
         */
    if (!$isRoot) {
        return false;
    }
        /* Current situation:
         * - the folder that needs to be added has a parent set
         * - we looped through the whole array recursively and parent does not exist
         *
         * => throw exception, possible reasons:
         * - query which provides the folders is not ordered properly to be processed
         * - inconsistent data (database)
         *     - loop between parents
         *     - database allows to set parent even though it does not exist
         */
    throw new ServerErrorHttpException('Folder tree could not be built.');
}

    /**
     * Processes the whole data stored in <code>$folderRecursiveData</code> and adds it to <code>$result</code>.
     *
     * @param array $result the array where the processed data of $folderRecursiveData should be added.
     * @param array $folderRecursiveData the complete data that needs to be processed and added to $result.
     * @param string $prefix A prefix that is used to know how deep in the hierarchy the current iteration is. Each
     *                       iteration adds 8 backspaces at the beginning of the value. A baseline prefix may be
     *                       specified here.
     * @throws \Exception
     */
    private static function processFolderArray(array &$result, array $folderRecursiveData, string $prefix = '')
    {

        uasort($folderRecursiveData, function ($item1, $item2) {
            return strtolower($item1[static::$indexValue]) <=> strtolower($item2[static::$indexValue]);
        });

        foreach ($folderRecursiveData as $folderId => $folderData) {
            $result[$folderId] = $prefix . ArrayHelper::getValue($folderData, static::$indexValue);
            self::processFolderArray($result, ArrayHelper::getValue($folderData, static::$indexChildren, []), "        $prefix");
        }
    }


    /**
     * Gets the parent folder.
     * @return FolderHierarchy|null
     */
    public function getParentFolder()
    {
        return self::findOne([
            'id' => $this->fk_folder,
        ]);
    }

    /**
     * Gets the child folders.
     * @return FolderHierarchy[]
     */
    public function getChildFolders()
    {
        return self::find()->where([
            'fk_folder' => $this->id,
        ])->all();
    }
    /**
     * Gets the representation of this object as String.
     * @return string|null
     */
    public function __toString()
    {
        return $this->name;
    }


        /**
     * {@inheritdoc}
     */
    public static function primaryKey()
    {
        return ["id"];
    }
}