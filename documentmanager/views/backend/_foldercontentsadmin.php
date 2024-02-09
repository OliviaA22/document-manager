<?php


use humhub\modules\documentmanager\helpers\DocumentManagerHelper;
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
use yii\widgets\Breadcrumbs;

DocumentManagerAsset::register($this);


/** @var $this yii\web\View */
/** @var $dataProvider yii\data\ArrayDataProvider */


$path = Folder::setBreadcrumbsPath(true)
?>
<div class="content-dummy-module-container">
    <div class="panel panel-default">

        <?= $path->run(); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => null,
            'columns' => [

                [
                    'attribute' => 'name',
                    'contentOptions' => ['class' => 'truncate'],
                    'format' => 'raw',
                    'value' => function ($model) {
                        $name = '';
                        if ($model instanceof Folder) {
                            $name = '<i class="fa fa-folder"></i>  '; 
                            $name .= Html::a($model->name, ['get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid(), 'fk_folder' => $model['id']], ['class' => 'folder-link', 'data-folder-id' => $model['id'], 'style' => 'text-decoration: none;']);
                        } elseif ($model instanceof DocumentRevision) {
                            $name = '<i class="fa fa-file-o"></i>  ';
                            $name .= Html::a($model->name, ['revision/download', 'cguid' => DocumentManagerHelper::getCGuid(), 'id' => $model['id'], 'version' => $model['version']], ['download' => true, 'style' => 'text-decoration: none;']);
                            $name .= '  ' . Html::tag('i', '', [
                                'class' => 'fa fa-info-circle',
                                'data-toggle' => 'tooltip',
                                'title' => $model->comment,
                                'style' => 'cursor:pointer'
                            ]);
                        } else {
                            throw new \Exception('Unsupported Object');
                        }
                        return $name;
                    },
                ],


                [
                    'attribute' => 'version',
                    'format' => 'raw',
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
                    'class' => ActionColumn::class,
                    'template' => '{create} {update} {delete}',
                    'buttons' => [
                        'create' => function ($url, $model, $key) {

                            if ($model instanceof DocumentRevision) {
                                return Html::a(
                                    '<span><i class="fa fa-plus-square-o" aria-hidden="true"></i></span>',
                                    ['/documentmanager/backend/create', 'cguid' => DocumentManagerHelper::getCGuid(), 'fk_document' => $model->fk_document],
                                );
                            }
                        }
                    ],
                    'visibleButtons' => [
                        'update' => function ($model, $key, $index) {
                            return $model instanceof DocumentRevision;
                        },
                        'delete' => function ($model, $key, $index) {
                            return true;
                        },
                    ],
                    'urlCreator' => function ($action, $model, $key, $index, $column) {
                        return Url::toRoute([$action, 'cguid' => DocumentManagerHelper::getCGuid(), 'id' => $model->id]);
                    }
                ]

            ],
        ]);
        ?>
    </div>

</div>