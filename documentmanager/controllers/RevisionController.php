<?php

namespace humhub\modules\documentmanager\controllers;



use humhub\modules\documentmanager\helpers\DocumentManagerHelper;
use humhub\modules\documentmanager\models\Document;
use humhub\modules\documentmanager\models\DocumentRevision;
use humhub\modules\documentmanager\models\Folder;
use humhub\modules\documentmanager\models\AffiliationDocument;
use humhub\modules\documentmanager\models\FolderHierarchy;
use humhub\modules\documentmanager\models\Revision;
use yii\web\NotFoundHttpException;

use yii\data\ArrayDataProvider;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\helpers\Url;

/**
 * Description of a Base Controller for the documentmanager module.
 */
class RevisionController extends \humhub\modules\content\components\ContentContainerController
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
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }




    /**
     * Creates a new Revision model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     */

    public function actionCreate()
    {

        $model = new DocumentRevision();

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
                    return $this->redirect(['backend/index', 'cguid' => DocumentManagerHelper::getCGuid()]);
                } else {
                    Yii::$app->session->setFlash('error', 'Error saving the document information to the database.');
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('revisioncreate', [
            'model' => $model,
        ]);
    }




    // Handles download
    public function actionDownload($id)
    {

        $model = $this->findModel($id);
        $document = $model->document;

        if ($document) {
            // Prepare the HTTP response to send the document content as a file
            Yii::$app->response->sendContentAsFile($model->document_content, $document->name, [
                'inline' => false,
                'mimeType' => 'application/octet-stream',  // Adjust the MIME type as needed
            ]);
        } else {
            // Handle the case when the document is not found
            Yii::$app->session->setFlash('error', 'Document not found.');
            return $this->redirect(['backend/_foldercontentsadmin', 'cguid' => DocumentManagerHelper::getCGuid()]); // Redirect to the index page or any other page you prefer
        }
    }


    /**
     * Deletes an existing Revision model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['backend/_foldercontentsadmin', 'cguid' => DocumentManagerHelper::getCGuid()]);
    }
    /**
     * Finds the Revision model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Revision the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Revision::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }




    /*

        $path = new Breadcrumbs();

        $cguid = DocumentManagerHelper::getCGuid();
        $fk_folder = Yii::$app->getRequest()->getQueryParam('fk_folder');
        $name = Yii::$app->getRequest()->getQueryParam('name');

        $path->homeLink = [
            [
                'label' => Yii::t('DocumentmanagerModule.app', 'Document Manager'),
                'url' => [
                    '/documentmanager/frontend/get-contents', 'cguid' => $cguid
                ]
            ],
            $path->links[] = [
                'label' => '_' . $name,
                'url' => [
                    '/documentmanager/frontend/get-contents', 'cguid' => $cguid, 'fk_folder' => $fk_folder
                ]
                ],
        
        ];



    // public static function getContentSettings() {

    //     $folders = $currentSpace;

    //     $settings = new SettingsForm(['contentContainer' => $currentSpace->contentContainer]);
    //     $settings->loadBySettings();
    //     if ($settings->displayEventsAdmin) {
    //         $crumbsurl = ['/documentmanager/backend/get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid(), 'id' => $folders->id];
    //     }else{
    //         $crumbsurl = ['/documentmanager/frontend/get-contents', 'cguid' => DocumentManagerHelper::getCGuid(), 'id' => $folders->id];
    //     }

    //     return $crumbsurl;
    // }


    public function getBreadcrumbs()
    {
        $breadcrumbs = $this->getBreadcrumbLinks();


        return $this->render('_foldercontentsadmin', [
            'breadcrumbs' => $breadcrumbs
        ]);
    }



    protected function getBreadcrumbLinks()
    {
        $fk_folder = Yii::$app->getRequest()->getQueryParam('fk_folder');
        $name = Yii::$app->getRequest()->getQueryParam('name');

        $path = new Breadcrumbs();
        $path->homeLink = [
            'label' => Yii::t('DocumentmanagerModule.app', 'Document Manager'),
            'url' => [
                '/documentmanager/backend/get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid()
            ]
        ];

        // Fetch parent folders recursively
        $folder = Folder::findOne($fk_folder);
        while ($folder !== null) {
            array_unshift($path->links, [
                'label' => $name,
                'url' => [
                    '/documentmanager/backend/get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid(), 'fk_folder' => $folder->fk_folder
                ]
            ]);
            $folder = $folder->fk_folder;
        }

        // Add current folder
        $path->links[] = [
            'label' => $name,
            'url' => [
                '/documentmanager/backend/get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid(), 'fk_folder' => $fk_folder
            ]
        ];

        return $path;
    }

    public static function getFolderName($id)
    {
        $folder = self::findOne($id);
        return $folder ? $folder->name : null;
        // return $folder->name;
    }



    
    public function getBreadcrumbLinks()
    {
        $links = [];
        $folders = $this;  $folder = Folder::findOne($fk_folder);

            $crumbsurl = BackendController::getContentSettings();
        while ($folders !== null) {
            $links[] = [
                'label' => $folder->name,
                'url' => $crumbsurl
            ];
            $folder = $folder->fk_folder;
        }

        $links = array_reverse($links);

        return $links;
    }



    public function getBreadcrumbLinks($id)
    {
    $links = [];

  $folder = Folder::findOne($id);
  
        $crumbsurl = BackendController::getContentSettings();
    while ($fk_folder !== null) {
        $links[] = [
            'label' => $folder->name,
            'url' => $crumbsurl
        ];
        $folder = $folder->fk_folder;
    }

    $links = array_reverse($links);

    return $links;
    }

    public static function getContentSettings($currentSpace) {

        $folders = $currentSpace;

        $settings = new SettingsForm(['contentContainer' => $currentSpace->contentContainer]);
        $settings->loadBySettings();
        if ($settings->displayEventsAdmin) {
            $crumbsurl = ['/documentmanager/backend/get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid(), 'id' => $folders->id];
        }else{
            $crumbsurl = ['/documentmanager/frontend/get-contents', 'cguid' => DocumentManagerHelper::getCGuid(), 'id' => $folders->id];
        }

        return $crumbsurl;
    }
    ////echo "<pre>";
        //print_r($fk_folder);
        //echo "</pre>";
        //die;

       //= Breadcrumbs::widget([
            //'links' => $folder->getBreadcrumbLinks(),
       // ]);
        


           public function actionSendNotification()
    {


        $models = Revision::find()->where(['is_informed' => 1])->all();

        $contentContainerModules = ContentContainerModuleState::find()
            ->where(['module_id' => 'documentmanager'])
            ->andWhere(['module_state' => 1])
            ->all();

        foreach ($contentContainerModules as $contentContainerModule) {

            $space = Space::find()->where([
                'contentcontainer_id' => $contentContainerModule->contentcontainer_id
            ])->one();

            if (!DocumentNotification::isFrontendEnabled($space)) {
                continue;
            }

            $users = DocumentNotification::getSpacedetails()->getMembershipUser(Membership::STATUS_MEMBER)->all();

            $originator = Yii::$app->user->identity; 

            foreach ($models as $model) {

                $notification = DocumentNotification::instance()->from($originator)->about($model);


                        echo '<pre>';
                        print_r($notification->space);
                        die;
                        echo '</pre>';

                $notification->sendBulk($users);

                $model->is_visible = intval($model->is_visible);

                $model->is_informed = intval(0);

                $model->save();
            }

        }



        Yii::$app->session->setFlash('success', 'Notifications sent successfully.');

        return $this->redirect(['//documentmanager/backend/index', 'cguid' => DocumentManagerHelper::getCGuid()]);
    }


    public function getUrl()
    {
        $contentContainerModules = ContentContainerModuleState::find()
            ->where(['module_id' => 'documentmanager'])
            ->andWhere(['module_state' => 1])
            ->all();


        foreach ($contentContainerModules as $contentContainerModule) {

            $space = Space::find()->where([
                'contentcontainer_id' => $contentContainerModule->contentcontainer_id
            ])->one();

            $fk_folder = Document::find()
                ->where(['id' => $this->source->fk_document])->one()->fk_folder;

            return Url::to(['/documentmanager/frontend/index', 'cguid' => $space->guid, 'fk_folder' => $fk_folder]);

        }

    }

    
    // public function getUrl()
    // {
    //     $urls = '';
    //     $spaces = DocumentNotification::getEnabledSpaces();
    //     foreach ($spaces as $space) {
    //         $fk_folder = Document::find()
    //             ->where(['id' => $this->source->fk_document])->one()->fk_folder;

    //        return Url::to(['/documentmanager/frontend/index', 'cguid' => $space->guid, 'fk_folder' => $fk_folder]);

    //     }
        // echo "<pre>";
        // print_r($this->fk_document);
        // echo "</pre>";
        // die; 

    //     return $urls;
    // }


    $path = new Breadcrumbs();
$path->homeLink = [
    'label' => Yii::t('DocumentmanagerModule.app', 'Document Manager'),
    'url' => [
        '/documentmanager/backend/get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid()
    ]
];

$fk_folder = Yii::$app->getRequest()->getQueryParam('fk_folder');
$folders = Folder::find()->where(['fk_folder' => $fk_folder])->all();

echo "<pre>";
print_r($folders);
echo "</pre>";
die;

foreach ($folders as $folder) {
    $label = $folder->name;
    $path->links[] = [
        'label' => $label,
        'url' => [
            '/documentmanager/backend/get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid(), 'id' => $folder->id
        ]
    ];
}


}

*/
}
