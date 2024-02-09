<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\documentmanager\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;


/**
 * Description of DocumentNotificationCategory
 *
 * @author buddha
 */
class DocumentNotificationCategory extends NotificationCategory
{

    /**
     * @inheritdoc
     */
    public $id = "new_document";

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('DocumentManagerModule.notifications', 'Receive Notifications for new documents.');
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('DocumentManagerModule.notifications', 'New Document');
    }

}
