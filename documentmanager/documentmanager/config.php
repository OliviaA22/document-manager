<?php

use humhub\commands\CronController;
use humhub\modules\documentmanager\controllers\BackendController;
use humhub\modules\documentmanager\controllers\FrontendController;
use humhub\modules\documentmanager\EventsAdmin;
use humhub\modules\space\widgets\Menu;

return [
    'id' => 'documentmanager',
    'class' => 'humhub\modules\documentmanager\Module',
    'namespace' => 'humhub\modules\documentmanager',
    'events' => [
        [
            'class' => Menu::class,
            'event' => Menu::EVENT_INIT,
            'callback' => [
                'humhub\modules\documentmanager\Events',
                'onSpaceMenuInit',
            ],
        ],
        [
            'class' => Menu::class,
            'event' => Menu::EVENT_INIT,
            'callback' => [
                'humhub\modules\documentmanager\EventsAdmin',
                'onSpaceMenuInit',
            ],
        ],


        [
            'class' => CronController::class,
            'event' => CronController::EVENT_BEFORE_ACTION,
            'callback' => 
            [
                EventsAdmin::class,
                'beforeQueueNotifications',
            ],            

        ],

    ],


    'settings' => [
        ['name' => 'displayEvents', 'type' => 'boolean', 'default' => true],
        ['name' => 'displayEventsAdmin', 'type' => 'boolean', 'default' => true],
    ],

];
