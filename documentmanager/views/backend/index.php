<?php

use humhub\modules\documentmanager\helpers\DocumentManagerHelper;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;


use yii\helpers\ArrayHelper;
use humhub\modules\documentmanager\models\Affiliation;
use humhub\modules\documentmanager\assets\DocumentManagerAsset;
use humhub\modules\documentmanager\models\Folder;

DocumentManagerAsset::register($this);

/** @var $this yii\web\View *
 * @var  humhub\modules\documentmanager\models\Folder $folders
 * @var humhub\modules\documentmanager\models\search\DocumentRevisionSearch $searchModel 
 * 

 */


?>
<div class="content-dummy-module-container">
    <div class="panel panel-default">



        <div class="panel-heading">
            <?= Html::a('<i class="icon fa fa-bell bell"></i>', ['backend/send-notification', 'cguid' => DocumentManagerHelper::getCGuid()], ['class' => ' pull-right btn btn-default notify']) ?>

            <h1>
                <?= Html::encode(Yii::t('DocumentmanagerModule.app', 'Document Manager')); ?>
            </h1>

            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div id="flash-message" class="alert alert-success alert-dismissable">
                    <?= '<i class="icon fa fa-check"></i>', Yii::$app->session->getFlash('success') ?>
                </div>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div id="flash-message" class="alert alert-danger alert-dismissable">
                    <?= '<i class="icon fa fa-xmark">', Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="frame-container">
            
            <div class="col-md-4 main-frame">

                <div class="document-search">
                    <?php $form = ActiveForm::begin([
                        'action' => ['file-search', 'cguid' => DocumentManagerHelper::getCGuid()],
                        'method' => 'get',
                        'options' => ['class' => 'search-form'],
                    ]); ?>

                    <?= $form->field($searchModel, 'search')->textInput(['placeholder' => 'Search by name or tags']) ?>
                    <?= $form->field($searchModel, 'fk_affiliation')->checkBoxList(
                        ArrayHelper::map(Affiliation::find()->all(), 'id', 'name')
                    ) ?>
                    <?= Html::submitButton('Search', ['class' => 'btn btn-primary btn-sm search-button']) ?>

                    <div class="btn-group pull-right">
                        <button class="btn btn-primary dropdown-toggle add-new" type="button" data-toggle="dropdown">
                            Add <span><i class="fa fa-plus-square" aria-hidden="true"></i></span>
                        </button>
                        <div class="dropdown-menu add-new">
                            <?= Html::a('New Document', ['backend/create-document', 'cguid' => DocumentManagerHelper::getCGuid()], ['class' => 'dropdown-item']) ?>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>




                <ul class="folder-view">
                    <?php
                    function displayFolders($folders)
                    {
                        foreach ($folders as $folder) {
                            $link = Html::tag('a', Html::encode($folder->name), [
                                'class' => 'documentmanager-folder-updater',
                                'href' => Url::to([
                                    '/documentmanager/backend/get-contents-admin',
                                    'cguid' => DocumentManagerHelper::getCGuid(),
                                    'fk_folder' => $folder->id,
                                ]),
                            ]);


                            $collapseIcon = '<i class="fa fa-chevron-right collapse-icon"  aria-hidden="true"></i>';

                            $linkcollapse = Html::tag('a', $collapseIcon, [
                                'class' => 'collapseClass',
                                'data-toggle' => 'collapse',
                                'data-target' => '#folder-' . $folder->id,
                            ]);

                            $folderIcon = '<i class="fa fa-folder folder-icon"></i>';



                            $subfolders = Folder::find()->where(['fk_folder' => $folder->id])->all();
                            if (!empty($subfolders)) {
                                echo Html::beginTag('li', ['class' => 'treeview']);
                                echo $linkcollapse . ' ' . $folderIcon . ' ' . $link;
                                echo Html::beginTag('ul', [
                                    'id' => 'folder-' . $folder->id,
                                    'class' => 'collapse'
                                ]);
                                displayFolders($subfolders);
                            } else {
                                echo Html::beginTag('li', ['class' => 'treeview']);
                                if (empty($folder->fk_folder)) {
                                    echo '   ' . $folderIcon . ' ' . $link;
                                } else {
                                    echo '   ' . $folderIcon . ' ' . $link;
                                }

                                echo Html::beginTag('ul', [
                                    'id' => 'folder-' . $folder->id,
                                    'class' => 'collapse'
                                ]);
                            }

                            echo Html::endTag('ul');
                            echo Html::endTag('li');
                        }
                    }

                    displayFolders($folders);

                    ?>
                </ul>

            </div>

            <div class="col-md-8 frame-box">
                <?php
                $fk_folder = Yii::$app->getRequest()->getQueryParam('fk_folder');
                if (isset($fk_folder)) {
                    echo Html::tag('iframe', null, [
                        'id' => 'documentmanager-iframe',
                        'src' => Url::to(['/documentmanager/backend/get-contents-admin', 'fk_folder' => $fk_folder, 'cguid' => DocumentManagerHelper::getCGuid()]),
                    ]);
                } else {
                    echo Html::tag('iframe', null, [
                        'id' => 'documentmanager-iframe',
                        'src' => Url::to(['/documentmanager/backend/get-contents-admin', 'cguid' => DocumentManagerHelper::getCGuid()]),
                    ]);
                }
                ?>
            </div>
        </div>
    </div>
</div>