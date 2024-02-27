<?php

namespace humhub\modules\documentmanager\controllers;

use humhub\modules\content\components\ContentContainerController;
use humhub\modules\documentmanager\EventsAdmin;
use humhub\modules\documentmanager\helpers\DocumentManagerHelper;
use humhub\modules\documentmanager\models\Affiliation;
use humhub\modules\documentmanager\models\Document;
use humhub\modules\documentmanager\models\DocumentRevision;
use humhub\modules\documentmanager\models\Folder;
use humhub\modules\documentmanager\models\AffiliationDocument;
use humhub\modules\documentmanager\models\CronSchedule;
use humhub\modules\documentmanager\models\FolderHierarchy;
use humhub\modules\documentmanager\models\Revision;


use humhub\modules\documentmanager\models\search\DocumentRevisionSearch;
use humhub\modules\documentmanager\models\SettingsForm;
use humhub\modules\documentmanager\notifications\DocumentNotification;
use humhub\modules\space\models\Membership;

use yii\console\Response;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;

use yii\web\NotFoundHttpException;

use yii\data\ArrayDataProvider;
use Yii;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * BackendController implements the CRUD actions for the admin interface.
 */
class BackendController extends ContentContainerController
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
     * Allows admin users to interact with and manage documents.
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
     * Performs a file search.
     * 
     * @return string
     */
    public function actionFileSearch()
    {

        $this->layout = 'main';
        $this->requireContainer = false;

        $searchModel = new DocumentRevisionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);


        return $this->render('_foldercontentsadmin', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Creates a new revision for an existing document.
     * If creation is successful, the browser will be redirected to the 'get-contents-admin' page.
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

                $files = UploadedFile::getInstances($model, 'document_content'); // Handle multiple files

                if (!empty($files)) {
                    foreach ($files as $file) {
                        $newRevisionInstance = new DocumentRevision();  // Create a new model instance for each file
                        $newRevisionInstance->fk_document = $document->id;

                        $fileContent = file_get_contents($file->tempName);
                        $encodedContent = base64_encode($fileContent);
                        $newRevisionInstance->document_content = $encodedContent;
                        $newRevisionInstance->is_informed = $model->is_informed;
                        $newRevisionInstance->is_visible = $model->is_visible;
                        $newRevisionInstance->fk_affiliation = $model->fk_affiliation;
                        $newRevisionInstance->version = $model->version;
                        $newRevisionInstance->tags = $model->tags;
                        $newRevisionInstance->comment = $model->comment;
                        $newRevisionInstance->created_date = date('Y-m-d H:i:s');

                        if (!$newRevisionInstance->save()) {
                            Yii::$app->session->setFlash('error', 'Error saving the document information to the database.');
                            return $this->redirect(['get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid()]);
                        }
                    }
                    Yii::$app->session->setFlash('success', 'Revisions created and saved successfully.');
                    return $this->redirect(['get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid()]);
                } else {
                    Yii::$app->session->setFlash('error', 'No files uploaded.');
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
     * Creates a new Document, including its revisions and affiliations.
     * If creation is successful, the browser will be redirected to the 'backend/index' page.
     * @return string|\yii\web\Response
     * @throws Exception if there is an error during the saving process.
     */
    public function actionCreateDocument()
    {
        $hierarchyModel = new FolderHierarchy();
        $folderModel = new Folder();
        $model = new Document();
        $revisionModel = new Revision();

        if ($this->request->isPost) {
            $files = UploadedFile::getInstances($revisionModel, 'document_content');

            if (!empty($files)) {
                $folderModel = $this->handleFolderCreation($hierarchyModel, $folderModel);

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $this->saveDocumentsAndRevisions($files, $folderModel, $model, $revisionModel);
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'All documents saved successfully');
                    return $this->redirect(['backend/index', 'cguid' => DocumentManagerHelper::getCGuid()]);
                } catch (Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            } else {
                Yii::$app->session->setFlash('error', 'No files uploaded.');
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('/document/documentcreate', [
            'model' => $model,
            'folderModel' => $folderModel,
            'revisionModel' => $revisionModel,
            'hierarchyModel' => $hierarchyModel,
        ]);
    }

    /**
     * Creates a new folder based on the provided or user-defined hierarchy.
     * @param FolderHierarchy $hierarchyModel The hierarchy model used for folder ordering.
     * @param Folder $folderModel The folder model to be created or updated.
     * @return Folder The folder model after creation or update.
     */
    private function handleFolderCreation($hierarchyModel, $folderModel)
    {
        if ($hierarchyModel->load($this->request->post())) {
            if ($this->request->post('create-new-folder-checkbox')) {
                $folderModel = Folder::createFromHierarchy($hierarchyModel);
                $folderModel->sub_level = empty($hierarchyModel->fk_folder) ? $folderModel->fk_folder : Folder::findOne($folderModel->fk_folder)->sub_level;
                if (!$folderModel->save()) {
                    Yii::$app->session->setFlash('error', 'Failed to save new folder.');
                }
            } else {
                $folderModel = $this->getExistingFolder($hierarchyModel->fk_folder);
            }
        }
        return $folderModel;
    }

    /**
     * Retrieves an existing folder by its ID.
     * @param int $folderId The ID of the folder to retrieve.
     * @throws Exception if the folder does not exist.
     * @return Folder|null The existing folder if found, null otherwise.
     */
    private function getExistingFolder($folderId)
    {
        $existingFolder = Folder::findOne($folderId);
        if ($existingFolder) {
            return $existingFolder;
        } else {
            Yii::$app->session->setFlash('error', 'Selected folder does not exist.');
            return null;
        }
    }

    /**
     * Saves documents and their revisions based on the provided files and models.
     * @param UploadedFile[] $files The uploaded files to be saved as document_content.
     * @param Folder $folderModel The folder model where the documents will be saved.
     * @param Document $documentModel The document model to be saved.
     * @param Revision $revisionModel The revision model to be saved.
     * @throws Exception if there is an error during the saving process.
     */
    private function saveDocumentsAndRevisions($files, $folderModel, $documentModel, $revisionModel)
    {
        foreach ($files as $file) {
            $documentModel = new Document();
            $revisionModel = new Revision();

            $this->loadDocumentData($documentModel, $file, $folderModel);
            if ($documentModel->save()) {
                $this->loadRevisionData($revisionModel, $file, $documentModel);
                if (!$revisionModel->save()) {
                    throw new Exception('Failed to save document revision.');
                }
                $this->handleDocumentAffiliations($documentModel);
            } else {
                throw new Exception('Failed to save document.');
            }
        }
    }

    /**
     * Assigns the document name from an uploaded file and also loads other document attributes into the document model.
     *
     * @param Document $documentModel The document model to load data into.
     * @param UploadedFile $file The uploaded file containing the document data.
     * @param Folder $folderModel The folder model associated with the document.
     */
    private function loadDocumentData($documentModel, $file, $folderModel)
    {
        $documentModel->name = $file->baseName . '.' . $file->extension;
        $documentModel->fk_folder = $folderModel->id;
        $documentModel->tags = Yii::$app->request->post('Document')['tags'];
        $documentModel->sub_level = $folderModel->sub_level;
    }

    /**
     * Loads revision data from the form into the revision model.
     * @param Revision $revisionModel The revision model to load data into.
     * @param UploadedFile $file The uploaded file containing the revision data.
     * @param Document $documentModel The document model associated with the revision.
     */
    private function loadRevisionData($revisionModel, $file, $documentModel)
    {
        $revisionModel->fk_document = $documentModel->id;
        $revisionModel->document_content = base64_encode(file_get_contents($file->tempName));
        $revisionModel->version = Yii::$app->request->post('Revision')['version'];
        $revisionModel->is_visible = Yii::$app->request->post('Revision')['is_visible'];
        $revisionModel->is_informed = Yii::$app->request->post('Revision')['is_informed'];
        $revisionModel->comment = Yii::$app->request->post('Revision')['comment'];
        $revisionModel->created_date = date('Y-m-d H:i:s');
    }

    /**
     * Handles the affiliations for a document model.
     * @param Document $documentModel The document model to assign the affiliations for.
     * @throws Exception if there is an error during the affiliation process.
     */
    private function handleDocumentAffiliations($documentModel)
    {
        $documentModel->documentAffiliations = $this->request->post('Document')['affiliations'];
        if (!empty($documentModel->documentAffiliations)) {
            foreach ($documentModel->documentAffiliations as $affiliationId) {
                $affiliationDocument = new AffiliationDocument();
                $affiliationDocument->fk_affiliation = $affiliationId;
                $affiliationDocument->fk_document = $documentModel->id;
                if (!$affiliationDocument->save()) {
                    Yii::$app->session->setFlash('error', 'Affiliation creation failed.');
                }
            }
        }
    }

    /**
     * Updates a specific record based on the provided ID.
     *
     * @param int $id The ID of the record to be updated.
     * @return string|Response The rendering result or the redirection response after the update.
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
                $existingAffiliationDocument = AffiliationDocument::find()->where(['fk_document' => $model->fk_document])->all();

                if ($documentModel instanceof Document) {
                    $this->handleDocumentAffiliations($documentModel);
                } 
                foreach ($existingAffiliationDocument as $affiliation) {
                    if (!in_array($affiliation->id, (array) $documentModel->documentAffiliations)) {
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
     * Creates a trigger for instant notification when needed
     * @return Response The response object that redirects to the index page.
     */
    public function actionSendNotification()
    {
        EventsAdmin::sendNotifications();
        Yii::$app->session->setFlash('success', 'Notifications sent successfully.');
        return $this->redirect(['index', 'cguid' => DocumentManagerHelper::getCGuid()]);
    }

    /**
     * Deletes selected object based on its specific model.
     * If deletion is successful, the browser will be redirected to the specified page.
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

                $parentFolder = $model->fk_folder;
                $folder_document = Document::find()->where(['fk_folder' => $model->id])->all();

                foreach ($folder_document as $document) {
                    $doc_revision = $document->checkRevision();

                    if (empty($doc_revision)) {

                        foreach ($document->affiliationDocuments as $affiliation) {
                            $affiliation->delete();
                        }
                        $document->delete();

                    } else {
                        Yii::$app->session->setFlash('error', 'Cannot delete folder that is not empty');
                        return $this->redirect(['backend/get-contents-admin', 'fk_folder' => $parentFolder, 'cguid' => DocumentManagerHelper::getCGuid()]);
                    }
                }
                $model->delete();
                return $this->redirect(['backend/get-contents-admin', 'fk_folder' => $parentFolder, 'cguid' => DocumentManagerHelper::getCGuid()]);

            } elseif ($model instanceof Revision) {

                $temp_document = $model->document;
                $parentFolder = $model->document->fk_folder;
                $model->delete();
                $doc_revision = $temp_document->checkRevision();

                if (empty($doc_revision)) {

                    foreach ($temp_document->affiliationDocuments as $affiliation) {
                        $affiliation->delete();
                    }
                    $temp_document->delete();
                }


                return $this->redirect(['backend/get-contents-admin', 'fk_folder' => $parentFolder, 'cguid' => DocumentManagerHelper::getCGuid()]);
            }
        } else {
            // Handle the case where the record is not found
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $this->redirect(['backend/get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid()]);
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
