<?php

namespace humhub\modules\documentmanager;

use humhub\modules\documentmanager\models\ConfigureForm;
use humhub\modules\documentmanager\models\CronSchedule;
use humhub\modules\documentmanager\models\Revision;
use humhub\modules\documentmanager\models\SettingsForm;
use humhub\modules\documentmanager\notifications\DocumentNotification;
use humhub\modules\space\models\Membership;
use humhub\modules\user\models\User;
use Yii;
use yii\base\ActionEvent;
use yii\db\Exception;

class EventsAdmin extends \yii\base\BaseObject
{
    /**
     * Adds the entrypoint to the form.
     *
     * @param $event
     */
    public static function onSpaceMenuInit($event)
    {

        $module = Yii::$app->getModule('documentmanager');

        if ($event->sender->space !== null && $event->sender->space->isModuleEnabled('documentmanager') && $event->sender->space->isMember()) {
            $settings = new SettingsForm(['contentContainer' => $event->sender->space]);
            $settings->loadBySettings();

            if ($settings->displayEventsAdmin) {

                $event->sender->addItem([
                    'label' => Yii::t('DocumentmanagerModule.app', 'Document Manager Admin'),
                    'group' => 'modules',
                    'url' => $event->sender->space->createUrl('/documentmanager/backend/index'),
                    'icon' => '<i class="fa fa-folder" aria-hidden="true"></i>',
                    'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'documentmanager')
                ]);
            }
        }
    }

    /**
     * Determines whether to proceed with queuing notifications based on the schedule logic.
     * If the schedule logic allows, it triggers the queuing of notifications.
     *
     * @param ActionEvent $event The event parameter typically provided in Yii2 event callbacks.
     */
    public static function beforeQueueNotifications(ActionEvent $event)
    {
        if (!static::scheduleLogic()) {
            $event->isValid = false;
        } else {
            static::queueNotifications();
        }
    }

    /**
     * Queues the notifications for the CronJob
     * sends notification to users, and updates the CronSchedule model.
     *
     * @return void
     */

    public static function queueNotifications()
    {
        list($models, $spaces) = self::getModelsAndSpaces();

        foreach ($spaces as $space) {
            $users = $space->getMembershipUser(Membership::STATUS_MEMBER)->all();

            foreach ($models as $model) {
                $notification = static::createNotification($model, $space->id);
                $notification->sendBulk($users);
                static::updateModel($model);
            }
        }
    }

    /**
     * Sends out instant notifications to all users in bulk using the `sendBulk` method of the Yii notification component, 
     * and updates the CronSchedule model.
     * @return void
     */
    public static function sendNotifications()
    {
        list($models, $spaces) = static::getModelsAndSpaces();

        foreach ($spaces as $space) {
            $users = $space->getMembershipUser(Membership::STATUS_MEMBER)->all();
            $userIds = static::getUserIds($users);

            foreach ($models as $model) {
                $notification = static::createNotification($model, $space->id);

                $query = static::getUserQuery($userIds);

                Yii::$app->notification->sendBulk($notification, $query);
                static::updateModel($model);
            }
        }
    }

    /**
     * Maps an array of users to their corresponding IDs.
     *
     * @param array $users An array of User objects or user IDs.
     * @return array An array of user IDs.
     */
    public static function getUserIds($users)
    {
        return array_map(function ($user) {
            return $user instanceof User ? $user->id : $user;
        }, $users);
    }

    /**
     * Returns a User query object that filters users by their IDs.
     *
     * @param array $userIds An array of user IDs to filter the query.
     * @return \yii\db\ActiveQuery The User query object filtered by the provided user IDs.
     */
    public static function getUserQuery($userIds)
    {
        return User::find()->where(['IN', 'user.id', $userIds]);
    }

    /**
     * Retrieves and returns an array containing two elements: a list of visible and informed models, and a list of enabled spaces.
     *
     * @return array An array where the first element is an array of Revision models that are both visible and informed, and the second element is an array of enabled spaces from DocumentNotification.
     */
    private static function getModelsAndSpaces()
    {
        $models = Revision::find()
            ->where(['is_visible' => 1, 'is_informed' => 1])
            ->all();
        $spaces = DocumentNotification::getEnabledSpaces();

        return [$models, $spaces];
    }


    /**
     * Creates and returns a new DocumentNotification instance for a given model and space ID.
     *
     * @param mixed $model The model for which the notification is about.
     * @param int $spaceId The ID of the space associated with the notification.
     * @return DocumentNotification
     */
    private static function createNotification($model, $spaceId)
    {
        $notification = new DocumentNotification();
        $notification->about($model);
        $notification->record->space_id = $spaceId;
        return $notification;
    }

    /**
     * Updates the visibility and informed status of a model and saves the changes.
     *
     * @param mixed $model
     */
    private static function updateModel($model)
    {
        $model->is_visible = intval($model->is_visible);
        $model->is_informed = intval(0);
        $model->save();
    }


    /**
     * Scheduling logic for notifications. Determines whether the task should run based on the current time, and the next_run value from the database.
     * It checks the CronSchedule model in the database to determine if the cron job should run and if a new entry needs to be created based on the scheduling logic.
     * 
     * @return bool True if scheduling conditions are met, and the database has been successfully updated with a new entry, False otherwise.
     * @throws Exception Throws an exception if the database transaction fails.
     */

    protected static function scheduleLogic()
    {
        $taskName = 'queueNotifications';

        // Fetch the last entry from the database
        $lastEntry = CronSchedule::find()
            ->where(['task_name' => $taskName])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();

        if ($lastEntry !== null) {
            $now = time();
            $nextRun = strtotime($lastEntry['next_run']);

            if (empty($nextRun) || $now >= $nextRun) {
                return static::createNewEntry($taskName);
            }
        }
        if ($lastEntry === null) {
            return static::createNewEntry($taskName);
        }
        return false;
    }

    /**
     * Creates a new CronSchedule entry with a given task name.
     *
     * @param string $taskName The name of the task for which the CronSchedule entry is created.
     * @return bool Returns true if the new CronSchedule entry is successfully saved, false otherwise.
     *
     * The 'last_run' field is set to the current date and time, 
     * and the 'next_run' field is set to the next Friday at 20:00. The function uses a database transaction to ensure data consistency. 
     * If the new entry is successfully saved, the function commits the transaction and returns true. If the save operation fails, 
     * the function rolls back the transaction and returns false.
     */
    protected static function createNewEntry($taskName)
    {
        $now = time();
        $newEntry = new CronSchedule();

        $configModel = new ConfigureForm();

        $newEntry->task_name = $taskName;
        $newEntry->last_run = date('Y-m-d H:i:s', $now);
       $newEntry->next_run = $configModel->getNextRunTime();
       
        // Using database transaction for data consistency
        $transaction = CronSchedule::getDb()->beginTransaction();
        try {
            if ($newEntry->save()) {
                $transaction->commit();
                return true;
            } else {
                $transaction->rollBack();
            }
        } catch (Exception $e) {
            $transaction->rollBack();
        }
        return false;
    }

}
