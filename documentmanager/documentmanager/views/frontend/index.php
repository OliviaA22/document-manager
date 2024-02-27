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

/** @var $this yii\web\View 
 *  @var $folders humhub\modules\documentmanager\models\Folder 
 *  @var humhub\modules\documentmanager\models\search\DocumentSearch $searchModel 
 */
?>
<div class="content-dummy-module-container">

    <div class="panel panel-default">

        <div class="frame-container">

            <div class="panel-heading">
                <h1>
                    <?= Html::encode(Yii::t('DocumentmanagerModule.app', 'Document Manager')); ?>
                </h1>
            </div>



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
                                    '/documentmanager/frontend/get-contents',
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
                        'src' => Url::to(['/documentmanager/frontend/get-contents', 'fk_folder' => $fk_folder, 'cguid' => DocumentManagerHelper::getCGuid()]),
                    ]);
                } else {
                    echo Html::tag('iframe', null, [
                        'id' => 'documentmanager-iframe',
                        'src' => Url::to(['/documentmanager/frontend/get-contents', 'cguid' => DocumentManagerHelper::getCGuid()]),
                    ]);
                }
                ?>

            </div>

        </div>
    </div>
</div>