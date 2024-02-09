<?php

use humhub\commands\CronController;
use humhub\modules\documentmanager\controllers\BackendController;
use humhub\modules\documentmanager\controllers\FrontendController;
use humhub\modules\space\widgets\Menu;
use yii\base\Event;

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
            'class' => Event::class,
            'event' => CronController::EVENT_ON_HOURLY_RUN,
            'callback' => 
            [
                'humhub\modules\documentmanager\controllers\FrontendController',
                'actionCronSendNotification',
            ],            
            // [FrontendController::class, 'actionCronSendNotification'],

        ],


        
    ],
    // 'notifications' => [
    //     'class' => 'humhub\modules\documentmanager\notifications\DocumentNotification',
    // ],
    'settings' => [
        ['name' => 'displayEvents', 'type' => 'boolean', 'default' => true],
        ['name' => 'displayEventsAdmin', 'type' => 'boolean', 'default' => true],
    ],

];
