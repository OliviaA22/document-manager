<?php

namespace humhub\modules\documentmanager\notifications;


use humhub\modules\content\models\ContentContainerModuleState;
use humhub\modules\documentmanager\EventsAdmin;
use humhub\modules\documentmanager\helpers\DocumentManagerHelper;
use humhub\modules\documentmanager\models\Document;
use humhub\modules\documentmanager\models\DocumentRevision;
use humhub\modules\documentmanager\models\Folder;
use humhub\modules\documentmanager\models\SettingsForm;
use humhub\modules\documentmanager\jobs\SendCustomBulkNotification;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\models\Notification;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;

/** @var $this yii\web\View 
 *  @var $folders humhub\modules\documentmanager\models\Folder 

*/

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class DocumentNotification extends BaseNotification
{
    public $model;

    // Module Id (required)
    public $moduleId = "documentmanager";

    public $requireOriginator = false;


    /**
     * {@inheritDoc}
     */
    public function html()
    {
        $doc_name = Document::find()
            ->where(['id' => $this->source->fk_document])->one()->name;

        return Yii::t('DocumentmanagerModule.notifications', "A new document %someDocument%, <strong>version:</strong>%someVersion% has been uploaded. ", [
            '%someDocument%' => '<strong>' . Html::encode($doc_name) . '</strong>',
            '%someVersion%' => '<strong>' . Html::encode($this->source->version) . '</strong>',
        ]);
    }


/**
 * Checks if the frontend is enabled for a given event.
 * @param mixed $event The event to check for if the frontend event is enabled.
 * @return bool Returns true if the frontend is enabled, false otherwise.
 */
    public static function isFrontendEnabled($event)
    {

        $settings = new SettingsForm(['contentContainer' => $event]);
        $settings->loadBySettings();

        if ($settings->displayEvents) {
            return true;

        }
        return false;
    }

    /**
 * Retrieves a list of spaces where the 'documentmanager' module is enabled.
 *
 * @return Space[] An array of Space objects that have the 'documentmanager' module enabled.
 */
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


/**
 * Generates a URL for the frontend index of the document manager based on the current record.
 *
 * @throws NotFoundHttpException if the space associated with the current record does not exist.
 * @return string The URL to the document manager frontend index.
 */
    public function getUrl()
    {
        $space = Space::find()->where([
            'id' => $this->record->space_id
        ])->one();

        if ($space === null) {
            throw new NotFoundHttpException('Space with id "' . $this->record->space_id . '" does not exist.');
        }
        $fk_folder = Document::find()->where(['id' => $this->source->fk_document])->one()->fk_folder;
        return Url::to(['/documentmanager/frontend/index', 'cguid' => $space->guid, 'fk_folder' => $fk_folder]);
    }

    /**
     * {@inheritDoc}
     * 
     * Sends a bulk notification to a list of users or a user query.
     * @param array|ActiveQueryUser $query The user list or ActiveQueryUser instance to send notifications to.
     * @throws InvalidConfigException if no moduleId is provided.
     */
    public function sendBulk($query)
    {
        if (empty($this->moduleId)) {
            throw new InvalidConfigException('No moduleId given for "' . get_class($this) . '"');
        }

        if (!$query instanceof ActiveQueryUser) {
            /** @var array $query */
            Yii::debug('BaseNotification::sendBulk - pass ActiveQueryUser instead of array!', 'notification');

            // Migrate given array to ActiveQueryUser
            $userIds = EventsAdmin::getUserIds($query);
            $query = EventsAdmin::getUserQuery($userIds);

        }
        Yii::$app->queue->push(new SendCustomBulkNotification(['notification' => $this, 'query' => $query, 'space_id' => $this->record->space_id]));
    }


    /**
     * {@inheritDoc}
     * 
     *Retrieves the space ID associated with the current record.
     * @return int|null The space ID if available, null otherwise.
     */
    public function getSpaceId()
    {
        if ($this->record) {
            return $this->record->space_id;
        }
        return null;
    }
}
