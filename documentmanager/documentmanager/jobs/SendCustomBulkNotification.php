<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\documentmanager\jobs;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\queue\LongRunningActiveJob;
use humhub\modules\user\components\ActiveQueryUser;
use Yii;

/**
 * Description of SendNotification
 *
 *
 */
class SendCustomBulkNotification extends LongRunningActiveJob
{
    /**
     * @var BaseNotification Basenotification data as array.
     */
    public $notification;

    /**
     * @var ActiveQueryUser the query to determine which users should receive this notification
     */
    public $query;

    /**
     * @var int to which space id should the notification be attached to?
     */
    public $space_id;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->notification && $this->notification->record) {
            $this->notification->record->space_id = $this->space_id;
        }
        Yii::$app->notification->sendBulk($this->notification, $this->query);
    }
}
