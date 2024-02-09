<?php

namespace humhub\modules\documentmanager\notifications;


use humhub\modules\content\models\ContentContainerModuleState;
use humhub\modules\documentmanager\helpers\DocumentManagerHelper;
use humhub\modules\documentmanager\models\Document;
use humhub\modules\documentmanager\models\DocumentRevision;
use humhub\modules\documentmanager\models\Folder;
use humhub\modules\documentmanager\models\SettingsForm;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\models\Notification;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

/** @var $this yii\web\View 
 *  @var $folders humhub\modules\documentmanager\models\Folder 

*/

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class DocumentNotification extends BaseNotification
{
    public $model;

    // Module Id (required)
    public $moduleId = "documentmanager";

    public $requireOriginator = false;



    // public function html()
    // {
    //     return Yii::t('DocumentmanagerModule.base', "           A new document has been uploaded. ");
    // }

    // public function html()
    // {
    //     $doc_name = Document::find()
    //     ->where(['id' => $this->source->fk_document])->one()->name;

    //     return Yii::t('DocumentmanagerModule.notifications', "%someUser% uploaded a new document %someDocument%, <strong>version:</strong>%someVersion% . ", [
    //         '%someUser%' => '<strong>' . Html::encode($this->originator->displayName) . '</strong>',
    //         '%someDocument%' => '<strong>' . Html::encode($doc_name) . '</strong>',
    //         '%someVersion%' => '<strong>' . Html::encode($this->source->version) . '</strong>',
    //     ]);
    // }

    public function html()
    {
        $doc_name = Document::find()
            ->where(['id' => $this->source->fk_document])->one()->name;

        return Yii::t('DocumentmanagerModule.notifications', "A new document %someDocument%, <strong>version:</strong>%someVersion% has been uploaded. ", [
            '%someDocument%' => '<strong>' . Html::encode($doc_name) . '</strong>',
            '%someVersion%' => '<strong>' . Html::encode($this->source->version) . '</strong>',
        ]);
    }


    public static function isFrontendEnabled($event)
    {

        $settings = new SettingsForm(['contentContainer' => $event]);
        $settings->loadBySettings();

        if ($settings->displayEvents) {
            return true;

        }
        return false;



    }

    public static function getEnabledSpaces()
    {
        $enabledSpaces = [];
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
            $enabledSpaces[] = $space;
        }
        return $enabledSpaces;
    }


    public function getUrl()
    {
        $spaces = DocumentNotification::getEnabledSpaces();
        $fk_folder = Document::find()->where(['id' => $this->source->fk_document])->one()->fk_folder;

        foreach ($spaces as $space) {

            $url = Url::to(['/documentmanager/frontend/index', 'cguid' => $space->guid, 'fk_folder' => $fk_folder]);
        }
        return $url;            
    }

    
    // public function getUrl()
    // {

    //     $spaces = DocumentNotification::getEnabledSpaces();
    //     $fk_folder = Document::find()->where(['id' => $this->source->fk_document])->one()->fk_folder;
        
    //     foreach ($spaces as $space) {
    //         return Url::to(['/documentmanager/frontend/index', 'cguid' => $space->guid, 'fk_folder' => $fk_folder]);
    //     }
    // }


}
