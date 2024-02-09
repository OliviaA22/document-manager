<?php

namespace humhub\modules\documentmanager\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\models\ContentContainerModuleState;
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
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use yii\console\Response;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

use yii\data\ArrayDataProvider;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/**
 * Description of a Base Controller for the documentmanager module.
 */
class BackendController extends \humhub\modules\content\components\ContentContainerController
{

    /**
     * @return array[]
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $settings = new SettingsForm(['contentContainer' => $this->contentContainer]);
                            $settings->loadBySettings();
                            return $settings->displayEventsAdmin;
                        },
                    ],
                ],
            ],
            // other behaviors...
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($action && $action->id == 'get-contents-admin') {
            return true;
        }
        if ($action && $action->id == 'delete') {
            return true;
        }
        if ($action && $action->id == 'file-search') {
            return true;
        }
        if ($action && $action->id == 'create') {
            return true;
        }
        if ($action && $action->id == 'send-notification') {
            return true;
        }
        if ($action && $action->id == 'update') {
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

        $searchModel = new DocumentRevisionSearch();
        $folders = Folder::find()->where(['fk_folder' => null])->all();

        $fk_folder = Yii::$app->getRequest()->getQueryParam('fk_folder');
        $fk_affiliation = ArrayHelper::map(Affiliation::find()->all(), 'id', 'name');

        return $this->render('index', [
            'folders' => $folders,
            'searchModel' => $searchModel,
            'fk_affiliation' => $fk_affiliation,
            'fk_folder' => $fk_folder,
        ]);
    }


    /**
     * Displays the contents of the requested folder.
     * 
     * @return string
     */
    /**
     * Displays the contents of the requested folder.
     * 
     * @return string
     */
    public function actionGetContentsAdmin()
    {

        $this->layout = 'main';
        $this->requireContainer = false;

        $fk_folder = Yii::$app->getRequest()->getQueryParam('fk_folder');
        $folders = Folder::find()->where(['fk_folder' => $fk_folder])->all();
        $revisions = DocumentRevision::getAllRevisions($fk_folder);



        $dataProvider = new ArrayDataProvider([
            'allModels' => array_merge($folders, $revisions),
            'pagination' => false,
        ]);

        return $this->render('_foldercontentsadmin', [
            'dataProvider' => $dataProvider,

        ]);
    }


    /**
     * Creates a new Revision model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param $fk_document
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */

    public function actionCreate($fk_document)
    {

        $this->layout = 'main';
        $this->requireContainer = false;

        $document = Document::findOne($fk_document);
        if (!$document) {
            throw new NotFoundHttpException('The requested document does not exist.');
        }

        $model = new DocumentRevision();
        $model->fk_document = $document->id;

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->document_content = UploadedFile::getInstance($model, 'document_content'); // Gets an instance of the uploaded file

                if ($model->document_content) {
                    $fileContent = file_get_contents($model->document_content->tempName); // Save the content
                    $encodedContent = base64_encode($fileContent);
                    $model->document_content = $encodedContent;
                }

                $model->created_date = date('Y-m-d H:i:s'); // Set the Modified date (created_date) to the current date and time

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Revision created and saved successfully.');

                    return $this->redirect(['get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid()]);
                } else {
                    Yii::$app->session->setFlash('error', 'Error saving the document information to the database.');
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('/revision/revisioncreate', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Document model.
     * If creation is successful, the browser will be redirected to the 'backend/index' page.
     * @return string|\yii\web\Response
     */

    public function actionCreateDocument()
    {
        $model = new Document();
        $folderModel = new Folder();
        $revisionModel = new Revision();
        $hierarchyModel = new FolderHierarchy();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // Load data from the form into $hierarchyModel
                if ($hierarchyModel->load($this->request->post())) {


                    if ($this->request->post('create-new-folder-checkbox')) {

                        $folderModel = Folder::createFromHierarchy($hierarchyModel);

                        // echo '<pre>';
                        // print_r($hierarchyModel);
                        // die;
                        // echo '</pre>';
                        if (empty($hierarchyModel->fk_folder)) {
                            $folderModel->sub_level = $folderModel->fk_folder;
                        } else {
                            $folderModel->sub_level = Folder::findOne($folderModel->fk_folder)->sub_level;
                        }

                        if (!$folderModel->save()) {
                            Yii::$app->session->setFlash('error', 'Failed to save new folder.');
                        }

                        $model->fk_folder = $folderModel->id;
                    } else {
                        $existingFolder = Folder::findOne($hierarchyModel->fk_folder);
                        if ($existingFolder) {
                            $model->fk_folder = $existingFolder->id;
                        } else {
                            Yii::$app->session->setFlash('error', 'Selected folder does not exist.');
                        }
                    }
                }

                $fileUpload = UploadedFile::getInstance($revisionModel, 'document_content');
                $model->name = $fileUpload->baseName . '.' . $fileUpload->extension; //Extracts the name and extension of the uploaded file and saves it as the document name

                $model->sub_level = $model->fk_folder ? Folder::findOne($model->fk_folder)->sub_level : $folderModel->sub_level; // Assigns the document sub_level column based on the folder sub_level value
                $model->save();

                // Handles and saves the document information related to the revision table
                if ($revisionModel->load($this->request->post())) {
                    $revisionModel->fk_document = $model->id;
                    $revisionModel->document_content = $fileUpload;

                    if ($revisionModel->document_content) {
                        $fileContent = file_get_contents($revisionModel->document_content->tempName); // Save the content
                        $encodedContent = base64_encode($fileContent);
                        $revisionModel->document_content = $encodedContent;
                    }
                    $revisionModel->created_date = date('Y-m-d H:i:s');
                    $revisionModel->save();

                    // Handle the affiliations
                    $model->documentAffiliations = $this->request->post('Document')['affiliations'];
                    if (!empty($model->documentAffiliations)) {

                        foreach ($model->documentAffiliations as $affiliationId) {
                            // Create a new AffiliationDocument model and associate it
                            $affiliationDocument = new AffiliationDocument();
                            $affiliationDocument->fk_affiliation = $affiliationId;
                            $affiliationDocument->fk_document = $model->id;

                            if (!$affiliationDocument->save()) {
                                Yii::$app->session->setFlash('error', 'Affiliation creation failed.');
                            }
                        }
                    }
                    Yii::$app->session->setFlash('success', 'Document saved successfully');

                    return $this->redirect(['backend/index', 'cguid' => DocumentManagerHelper::getCGuid()]);
                }
            } else {
                Yii::$app->session->setFlash('error', 'Document creation failed.');
            }
        } else {
            $model->loadDefaultValues();
        }
        // DocumentNotification::instance()->from(Yii::$app->user->identity)->about($model)->sendBulk($users);
        return $this->render('/document/documentcreate', [
            'model' => $model,
            'folderModel' => $folderModel,
            'revisionModel' => $revisionModel,
            'hierarchyModel' => $hierarchyModel,
        ]);
    }


    public function actionSendNotification()
    {

        $models = Revision::find()
            ->where(['is_visible' => 1])
            ->where(['is_informed' => 1])->all();

        $spaces = DocumentNotification::getEnabledSpaces();

        foreach ($spaces as $space) {

            $users = $space->getMembershipUser(Membership::STATUS_MEMBER)->all();
            // $originator = Yii::$app->user->identity;

            foreach ($models as $model) {

                //  $notification = DocumentNotification::instance()->from($originator)->about($model);
                //$notification = DocumentNotification::instance()->about($model);
                $notification = new DocumentNotification();
                //  $notification->payload([
                //     'someValue' => 'test',
                // ]);
                $notification->about($model);
                foreach ($users as $user) {
                    $notification->send($user);
                }
                //$notification->sendBulk($users);
                $model->is_visible = intval($model->is_visible);
                $model->is_informed = intval(0);

                $model->save();
            }
            Yii::$app->session->setFlash('success', 'Notifications queued successfully.');
        }
        return $this->redirect(['index', 'cguid' => DocumentManagerHelper::getCGuid()]);
    }





    /**
     * @param $id
     * @return string|Response|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function actionUpdate($id)
    {
        $this->layout = 'main';
        $this->requireContainer = false;

        $model = $this->findModel($id);
        $documentModel = Document::find()->where(['id' => $model->fk_document])->one();

        $model->created_date = date('Y-m-d H:i:s');

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            if ($this->request->isPost && $documentModel->load($this->request->post()) && $documentModel->save()) {
                $documentModel->documentAffiliations = $this->request->post('Document')['affiliations'];

                $existingAffiliationDocument = AffiliationDocument::find()->where(['fk_document' => $model->fk_document])->all();
                if (!empty($documentModel->documentAffiliations)) {


                    foreach ($documentModel->documentAffiliations as $affiliationId) {

                        // Associate the affiliation with the revision
                        $affiliationDocument = new AffiliationDocument();

                        $affiliationDocument->fk_affiliation = $affiliationId;
                        $affiliationDocument->fk_document = $model->fk_document;

                        if (!$affiliationDocument->save()) {
                            Yii::$app->session->setFlash('error', 'Error saving affiliation information.');
                        }
                    }
                }

                foreach ($existingAffiliationDocument as $affiliation) {

                    // Associate the affiliation with the revision
                    if (!in_array($affiliation->fk_affiliation, (array) $documentModel->documentAffiliations)) {
                        $affiliation->delete();
                    }
                }
            }
            Yii::$app->session->setFlash('success', 'Updated successfully.');

            return $this->redirect(['get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid()]);
        }

        return $this->render('/revision/revisionupdate', [
            'model' => $model,
            'documentModel' => $documentModel,
        ]);
    }


    /**
     * Deletes selected object based on its specific model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    // public function actionDelete($id)
    // {
    //     $model = Revision::findOne($id);
    //     if ($model !== null) {
    //         $model->delete();
    //         Yii::$app->session->setFlash('success', 'Document revision deleted successfully.');
    //     }
    //     return $this->redirect(['backend/index']);
    // }




    /**
     * Deletes an existing Document model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Check if the record exists before deleting
        if ($model !== null) {
            if ($model instanceof Folder) {

                $model->delete();
                return $this->redirect(['backend/get-contents-admin', 'fk_folder' => $model->fk_folder, 'cguid' => DocumentManagerHelper::getCGuid()]);
            } elseif ($model instanceof Revision) {
                $model->delete();
                return $this->redirect(['backend/get-contents-admin', 'fk_folder' => $model->document->fk_folder, 'cguid' => DocumentManagerHelper::getCGuid()]);
            }
        } else {
            // Handle the case where the record is not found
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Finds the specific model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return DocumentRevision the loaded model
     * @return Folder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */

    protected function findModel($id)
    {
        $model = DocumentRevision::findOne($id);
        // If not found, attempt to find a Folder with the given ID
        if ($model === null) {
            $model = Folder::findOne($id);
        }
        // If no model is found at all, throw an exception
        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $model;
    }
}
