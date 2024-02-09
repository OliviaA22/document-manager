<?php

namespace humhub\modules\documentmanager\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\documentmanager\assets\DocumentManagerAsset;

use humhub\modules\documentmanager\helpers\DocumentManagerHelper;
use humhub\modules\documentmanager\models\Affiliation;
use humhub\modules\documentmanager\models\Document;
use humhub\modules\documentmanager\models\DocumentRevision;
use humhub\modules\documentmanager\models\Folder;
use humhub\modules\documentmanager\models\AffiliationDocument;
use humhub\modules\documentmanager\models\FolderHierarchy;
use humhub\modules\documentmanager\models\Revision;


use humhub\modules\documentmanager\models\search\DocumentRevisionSearch;
use humhub\modules\documentmanager\models\SettingsForm;
use humhub\modules\documentmanager\notifications\DocumentNotification;
use humhub\modules\notification\models\Notification;
use humhub\modules\space\models\Membership;
use yii\console\Response;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

use yii\data\ArrayDataProvider;
use Yii;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * Description of a Base Controller for the files module.
 */
class FrontendController extends \humhub\modules\content\components\ContentContainerController
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
     * Allows user to interact with a form. Shows success message if emails sent.
     *
     * @return string
     */
    public function actionIndex()
    {

        $fk_folder = Yii::$app->getRequest()->getQueryParam('fk_folder');
        $searchModel = new DocumentRevisionSearch();
        $folders = Folder::find()->where(['fk_folder' => null])->all();

        return $this->render('index', [
            'folders' => $folders,
            'searchModel' => $searchModel,
            'fk_folder' => $fk_folder,
        ]);
    }

    /**
     * Displays the contents of the requested folder.
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


    public function actionFileSearch()
    {

        $this->layout = 'main';
        $this->requireContainer = false;
        
        $searchModel = new DocumentRevisionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $affiliations = ArrayHelper::map(Affiliation::find()->all(), 'id', 'name');


        return $this->render('_foldercontents', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'affiliations' => $affiliations,
        ]);
    }
    
    public function actionCronSendNotification()
    {

        $models = Revision::find()
            ->where(['is_visible' => 1])
            ->where(['is_informed' => 1])->all();

        $spaces = DocumentNotification::getEnabledSpaces();

        foreach ($spaces as $space) {
            $users = $space->getMembershipUser(Membership::STATUS_MEMBER)->all();

            foreach ($models as $model) {
                $notification = new DocumentNotification();
                $notification->about($model);
                $notification->sendBulk($users);

                // $notification_space = new Notification;
                // $notification_space->space_id = $space->id;
                // $notification_space->save();

                $model->is_visible = intval($model->is_visible);
                $model->is_informed = intval(0);

                $model->save();
            }  
        }
    }


}
