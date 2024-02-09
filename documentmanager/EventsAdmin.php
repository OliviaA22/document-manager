<?php

namespace humhub\modules\documentmanager;

use humhub\modules\documentmanager\models\SettingsForm;
use Yii;

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
                    'isActive' => (Yii::$app->controller->module &&Yii::$app->controller->module->id == 'documentmanager')
                ]);
            }
        }
    }
    

}
