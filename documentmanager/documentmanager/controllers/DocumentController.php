<?php

namespace humhub\modules\documentmanager\controllers;


use humhub\modules\documentmanager\models\DocumentRevision;
use humhub\modules\documentmanager\models\Folder;

use humhub\modules\documentmanager\models\search\DocumentRevisionSearch;

use yii\data\ArrayDataProvider;
use Yii;
use yii\filters\VerbFilter;


/** 
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

        return parent::beforeAction($action);
    }


  
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

        return $this->render('/backend/_foldercontentsadmin', [
            'folders' => $folders,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }





}
