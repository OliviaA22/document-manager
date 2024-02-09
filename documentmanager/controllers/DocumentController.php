<?php

namespace humhub\modules\documentmanager\controllers;



use humhub\modules\documentmanager\helpers\DocumentManagerHelper;
use humhub\modules\documentmanager\models\Document;
use humhub\modules\documentmanager\models\DocumentRevision;
use humhub\modules\documentmanager\models\Folder;
use humhub\modules\documentmanager\models\Affiliation;
use humhub\modules\documentmanager\models\AffiliationDocument;
use humhub\modules\documentmanager\models\search\DocumentRevisionSearch;
use humhub\modules\documentmanager\models\FolderHierarchy;
use humhub\modules\documentmanager\models\Revision;
use yii\web\NotFoundHttpException;

use humhub\modules\documentmanager\models\search\DocumentSearch;

use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
use Yii;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\helpers\Url;

/**
 * Description of a Base Controller for the documentmanager module.
 * 
 * DocumentController implements the CRUD actions for Document model.
 */
class DocumentController extends \humhub\modules\content\components\ContentContainerController
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }


    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($action && $action->id == 'get-contents-admin') {
            return true;
        }
        // if ($action && $action->id == 'delete') {
        //     return true;
        // }
        if ($action && $action->id == 'file-search') {
            return true;
        }
        # return ContentContainerController::beforeAction($action);
        return parent::beforeAction($action);
    }


    /**
     * Allows user to interact with a form. Shows success message if emails sent.
     *
     * @return string
     */

    //  public function actionIndex()
    //  {

    //      $searchModel = new DocumentSearch();
    //      $folders = Folder::find()->where(['fk_folder' => null])->all();

    //      return $this->render('filesearch', [
    //          'folders' => $folders,
    //          'searchModel' => $searchModel,
    //      ]);
    //  }

    public function actionIndex()
    {
        $searchModel = new DocumentRevisionSearch();

        $fk_folder = Yii::$app->getRequest()->getQueryParam('fk_folder');

        if ($fk_folder === null) {
            $folders = Folder::find()->where(['fk_folder' => null])->all();
            $dataProvider = new ArrayDataProvider([
                'allModels' => $folders,
                'pagination' => false,
            ]);
        } else {
            $folders = Folder::find()->where(['fk_folder' => $fk_folder])->all();
            $revisions = DocumentRevision::getVisibleRevisions($fk_folder);
            $dataProvider = new ArrayDataProvider([
                'allModels' => array_merge($folders, $revisions),
                'pagination' => false,
            ]);
        }

        return $this->render('filesearch', [
            'folders' => $folders,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }



    /**
     * Displays the contents of the requested folder.
     * 
     * @return string
     */
    public function actionGetContentsAdmin()
    {
        $this->requireContainer = false;

        $fk_folder = Yii::$app->getRequest()->getQueryParam('fk_folder');

        $searchModel = new DocumentRevisionSearch();

        $folders = Folder::find()->where(['fk_folder' => $fk_folder])->all();
        $revisions = DocumentRevision::getVisibleRevisions($fk_folder);

        $dataProvider = new ArrayDataProvider([
            'allModels' => array_merge($folders, $revisions),
            'pagination' => false,
        ]);

        return $this->render('filesearch', [
            'dataProvider' => $dataProvider,
            'folders' => $folders,
            'searchModel' => $searchModel,
        ]);
    }



    public function actionFileSearch()
    {

        $this->layout = 'main';
        $this->requireContainer = false;

        $searchModel = new DocumentRevisionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);


        return $this->render('/backend/_foldercontentsadmin', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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
                        if ($hierarchyModel->fk_folder === null) {
                            $folderModel = Folder::createFromHierarchy($hierarchyModel);

                            $folderModel->save();
                            $folderModel->sub_level = $folderModel->fk_folder;

                            $model->fk_folder = $folderModel->id;
                        } else {
                            // Creating a new folder inside an existing folder
                            // $parentFolder = FolderHierarchy::findOne($hierarchyModel->fk_folder);

                            $folderModel = Folder::createFromHierarchy($hierarchyModel);
                            
                            $folderModel->save();       
                            $folderModel->sub_level = Folder::findOne($folderModel->fk_folder)->sub_level;

                            $model->fk_folder = $folderModel->id;
                        }
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
                    $revisionModel->fk_document =  $model->id;
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

                            if ($affiliationDocument->save()) {
                                // Association saved successfully
                            } else {
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

        return $this->render('documentcreate', [
            'model' => $model,
            'folderModel' => $folderModel,
            'revisionModel' => $revisionModel,
            'hierarchyModel' => $hierarchyModel,
        ]);
    }



    // Handles download
    public function actionDownload($id, $version)
    {
        $this->layout = 'main';
        $this->requireContainer = false;

        $model = $this->findModel($id);
        $document = Revision::findOne(['version' => $version]); // Finds the specific revision

        // Prepare the HTTP response to send the document content as a file
        if ($document) {
            Yii::$app->response->sendContentAsFile($document->document_content, $model->name, [
                'inline' => false,
                'mimeType' => 'application/octet-stream', // Adjust the MIME type as needed
            ]);
        } else {
            // Handle the case when the document is not found
            Yii::$app->session->setFlash('error', 'Document not found.');
            return $this->redirect(['backend/_foldercontentsadmin', 'cguid' => DocumentManagerHelper::getCGuid()]); // Redirect to the index page
        }
    }

    /**
     * Deletes an existing Document model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['/backend/index', 'cguid' => DocumentManagerHelper::getCGuid()]);
    }

    /**
     * Finds the Document model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Document the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Document::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
