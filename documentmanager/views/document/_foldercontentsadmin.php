<?php
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use humhub\modules\documentmanager\models\DocumentRevision;
use humhub\modules\documentmanager\models\Document;
use humhub\modules\documentmanager\models\Revision;
use humhub\modules\documentmanager\models\Folder;

use yii\grid\GridView;
use humhub\modules\documentmanager\assets\DocumentManagerAsset;

DocumentManagerAsset::register($this);

/** @var $this yii\web\View */
/** @var $dataProvider yii\data\ArrayDataProvider */

?>
<div class="content-dummy-module-container">
    <div class="panel panel-default">
    
        <div class="panel-heading">
            <h1><?= Html::encode(Yii::t('DocumentmanagerModule.app', 'Document Manager')); ?></h1>
        </div>
    

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => null,
            'columns' => [
                [
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if ($model instanceof Folder) {
                            return $model->name;
                            // return Html::a(Document::getNameWithHierachy($model), ['', 'id' => $model['id']], ['class' => 'folder', 'style' => 'text-decoration: none;']);
                        } elseif ($model instanceof DocumentRevision) {
                            return Html::a($model->name, ['/revision/download', 'id' => $model['id'], 'version' => $model['version']], ['download' => true, 'style' => 'text-decoration: none;']);
                            // return Html::a(Document::getNameWithHierachy($model), ['download', 'id' => $model['id'], 'version' => $model['version']], ['class' => 'document', 'style' => 'text-decoration: none;']);
                        } else {
                            throw new \Exception('Unsupported Object');
                        }
                    },
                ],
 
                [
                    'attribute' => 'version',
                    'format' => 'text',
                    'value' => function ($model) {
                        if ($model instanceof Folder) {
                            return '-';
                        } elseif ($model instanceof DocumentRevision) {
                            return $model->version;
                        } else {
                            throw new \Exception('Unsupported Object');
                        }
                    },
                ],
                [
                    'attribute' => 'tags',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if ($model instanceof Folder) {
                            return '-';
                        } elseif ($model instanceof DocumentRevision) {
                            $allTags = explode(',', $model->tags);
                            $html = '';
                            foreach ($allTags as $tag) {
                                $html .= ' ' . Html::tag('span', Html::encode($tag), [
                                    'class' => 'label label-primary',
                                ]);
                            }
                            if ($html) {
                                return $html;
                            }
                            return '-';
                        } else {
                            throw new \Exception('Unsupported Object');
                        }
                    },
                ],
                [
                    'attribute' => 'created_date',
                    'format' => 'datetime',
                    'value' => function ($model) {
                        if ($model instanceof Folder) {
                            return $model->created_date;
                        } elseif ($model instanceof DocumentRevision) {
                            return $model->created_date;
                        } else {
                            throw new \Exception('Unsupported Object');
                        }
                    },
                ],
                [

                    'class' => ActionColumn::className(),
                    'urlCreator' => function ($action, $model, $key, $index, $column) {
                            return Url::toRoute([$action, 'id' => $model->id]);
                    }
                ],
                
            ],
        ]);
    ?>
    </div>
</div>
