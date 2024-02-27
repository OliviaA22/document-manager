<?php

namespace humhub\modules\documentmanager\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\documentmanager\models\Affiliation;
use humhub\modules\documentmanager\models\DocumentRevision;
use humhub\modules\documentmanager\models\Folder;

use humhub\modules\documentmanager\models\search\DocumentSearch;
use yii\helpers\ArrayHelper;

use yii\data\ArrayDataProvider;
use Yii;


/**
 * FrontendController implements the CRUD actions for the users interface.
 */
class FrontendController extends ContentContainerController
{

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($action && $action->id == 'get-contents') {
            return true;
        }
        if ($action && $action->id == 'file-search') {
            return true;
        }
        return parent::beforeAction($action);
    }

    /**
     * Allows user to interact with folders.
     *
     * @return string
     */
    public function actionIndex()
    {

        $fk_folder = Yii::$app->getRequest()->getQueryParam('fk_folder');
        $searchModel = new DocumentSearch();
        $folders = Folder::find()->where(['fk_folder' => null])->all();

        return $this->render('index', [
            'folders' => $folders,
            'searchModel' => $searchModel,
            'fk_folder' => $fk_folder,
        ]);
    }

    /**
     * Displays the contents of the requested/selected folder.
     * 
     * @return string
     */
    public function actionGetContents()
    {

        $this->layout = 'main';
        $this->requireContainer = false;

        $fk_folder = Yii::$app->getRequest()->getQueryParam('fk_folder');

        $folders = Folder::find()->where(['fk_folder' => $fk_folder])->all();
        $revisions = DocumentRevision::getVisibleRevisions($fk_folder);
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_merge($folders, $revisions),
            'pagination' => false,
        ]);

        
        return $this->render('_foldercontents', [
            'dataProvider' => $dataProvider,

        ]);
    }

    /**
     * Performs a file search.
     * 
     * @return string
     */
    public function actionFileSearch()
    {

        $this->layout = 'main';
        $this->requireContainer = false;
        
        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $affiliations = ArrayHelper::map(Affiliation::find()->all(), 'id', 'name');


        return $this->render('_foldercontents', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'affiliations' => $affiliations,
        ]);
    }
    



}
