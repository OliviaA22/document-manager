<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;

use humhub\modules\documentmanager\models\DocumentRevision;

use yii\helpers\ArrayHelper;
use yii\grid\GridView;

use humhub\modules\documentmanager\models\Affiliation;
use humhub\modules\documentmanager\models\search\RevisionSearch;
use humhub\modules\documentmanager\assets\DocumentManagerAsset;
use humhub\modules\documentmanager\models\Folder;
use humhub\modules\documentmanager\models\Revision;

DocumentManagerAsset::register($this);

/** @var $this yii\web\View *
 * @var $folders humhub\modules\documentmanager\models\Folder 
 * @var humhub\modules\documentmanager\models\search\DocumentRevisionSearch $searchModel 
 * @var yii\data\ActiveDataProvider $dataProvider
 */


?>
<div class="content-dummy-module-container">
    <div class="panel">

        <div class="panel-heading">
            <h1><?= Html::encode(Yii::t('DocumentmanagerModule.app', 'Document Manager')); ?></h1>
        </div>

        <div class="col-md-4">


            <div class="document-search">
                <?php $form = ActiveForm::begin([
                    'action' => ['file-search'],
                    'method' => 'get',
                ]); ?>

                <?= $form->field($searchModel, 'search')->textInput(['placeholder' => 'Search by name or tags']) ?>
                <?= Html::submitButton('Search', ['class' => 'btn btn-primary btn-sm']) ?>
                <?php ActiveForm::end(); ?>
            </div>

            <!-- ToDo: Make it look nice -->
            <div class="btn-group pull-right" style="margin-top: 20px;">
                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown">
                    Add <span><i class="fa fa-plus-square" aria-hidden="true"></i></span>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <?= Html::a('New Revision', ['revision/create', 'cguid' => Yii::$app->getRequest()->getQueryParam('cguid')], ['class' => 'dropdown-item']) ?>
                    <div class="divider"></div>
                    <?= Html::a('New Document', ['document/create-document', 'cguid' => Yii::$app->getRequest()->getQueryParam('cguid')], ['class' => 'dropdown-item']) ?>
                </div>
            </div>
            <ul class="folder-view">
                <?php
                // function displayFolders($folders)
                // {
                //     foreach ($folders as $folder) {
                //         $link = Html::tag('a', Html::encode($folder->name), [
                //             'class' => 'documentmanager-folder-updater',
                //             'href' => Url::to([
                //                 '/documentmanager/document/get-content-admin',
                //                 'cguid' => Yii::$app->getRequest()->getQueryParam('cguid'),
                //                 'fk_folder' => $folder->id,
                //             ]),
                //         ]);

                //         $folderIcon = '<i class="fa fa-folder"></i>';

                //         echo Html::tag('li', $folderIcon . ' ' . $link);

                //         $subfolders = Folder::find()->where(['fk_folder' => $folder->id])->all();
                //         if (!empty($subfolders)) {
                //             echo '<ul>';
                //             displayFolders($subfolders);
                //             echo '</ul>';
                //         }
                //     }
                // }

                // displayFolders($folders);

                function displayFolders($folders, $isRoot = true)
                {
                    foreach ($folders as $folder) {
                        $link = Html::tag('a', Html::encode($folder->name), [
                            'class' => 'documentmanager-folder-updater',
                            'href' => Url::to([
                                '/documentmanager/document/get-content-admin',
                                'cguid' => Yii::$app->getRequest()->getQueryParam('cguid'),
                                'fk_folder' => $folder->id,
                            ]),
                            'data-toggle' => 'collapse',
                            'aria-expanded'=>'false',
                            'data-target' => '#folder-' . $folder->id,
                        ]);

                        $folderIcon = '<i class="fa fa-folder"></i>';

                        echo Html::beginTag('li', ['class' => 'treeview']);
                        echo $folderIcon . ' ' . $link;
                        echo Html::beginTag('ul', [
                            'id' => 'folder-' . $folder->id,
                            'class' => 'collapse'
                        ]);

                        $subfolders = Folder::find()->where(['fk_folder' => $folder->id])->all();
                        if (!empty($subfolders)) {
                            displayFolders($subfolders, false);
                        }

                        echo Html::endTag('ul');
                        echo Html::endTag('li');
                    }
                }

                displayFolders($folders);


                //    foreach ($folders as $folder) {
                //         if ($folder->fk_folder === null) {
                //             $link = Html::tag('a', Html::encode($folder->name), [
                //                 'class' => 'documentmanager-folder-updater',
                //                 'href' => Url::to([
                //                     '/documentmanager/frontend/get-contents',
                //                     'cguid' => Yii::$app->getRequest()->getQueryParam('cguid'),
                //                     'fk_folder' => $folder->id,
                //                 ]),
                //             ]);

                //             $folderIcon = '<i class="fa fa-folder"></i>';

                //             echo Html::tag('li',   $folderIcon . ' ' . $link);
                //         }
                //     }


                ?>
            </ul>

        </div>

        <div class="col-md-8" id="folder-contents">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => null,
                'columns' => [
                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'value' => function ($model) {
                            if ($model instanceof Folder) {
                                return Html::a($model->name, ['get-contents-admin','cguid' => Yii::$app->getRequest()->getQueryParam('cguid'), 'id' => $model['id']], ['class' => 'folder-link', 'data-folder-id' => $model['id'], 'style' => 'text-decoration: none;']);
                            } elseif ($model instanceof DocumentRevision) {
                                return Html::a($model->name, ['revision/download','cguid' => Yii::$app->getRequest()->getQueryParam('cguid'), 'id' => $model['id'], 'version' => $model['version']], ['download' => true, 'style' => 'text-decoration: none;']);
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

                        'class' => ActionColumn::class,
                        'urlCreator' => function ($action, $model, $key, $index, $column) {
                            return Url::toRoute([$action,'cguid' => Yii::$app->getRequest()->getQueryParam('cguid'),'id' => $model->id]);
                        }
                    ],

                ],
            ]);
            ?>

        </div>
    </div>
</div>