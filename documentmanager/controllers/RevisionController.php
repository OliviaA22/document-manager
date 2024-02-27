<?php

namespace humhub\modules\documentmanager\controllers;

use humhub\modules\documentmanager\helpers\DocumentManagerHelper;
use humhub\modules\documentmanager\models\Document;
use humhub\modules\documentmanager\models\DocumentRevision;
use humhub\modules\documentmanager\models\Revision;
use yii\web\NotFoundHttpException;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;

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
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    // Handles download
    public function actionDownload($id)
    {

        $model = $this->findModel($id);
        $document = $model->document;

        if ($document) {
            Yii::$app->response->sendContentAsFile($model->document_content, $document->name, [
                'inline' => false,
                'mimeType' => 'application/octet-stream',  // Adjust the MIME type as needed
            ]);
        } else {
            Yii::$app->session->setFlash('error', 'Document not found.');

        }
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

}
