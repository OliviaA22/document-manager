<?php

namespace humhub\modules\documentmanager;

use Yii;
use yii\helpers\Url;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\documentmanager\models\SettingsForm;

class Module extends ContentContainerModule
{
    /**
     * @inheritdoc
     */


    public function init()
    {
        parent::init();

    }


    public function getContentContainerTypes()
    {
        return [
            Space::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getContentContainerName(ContentContainerActiveRecord $container)
    {
        return 'Document Manager';
    }

    /**
     * @inheritdoc
     */
    public function getContentContainerDescription(ContentContainerActiveRecord $container)
    {
        if ($container instanceof Space) {
            return 'Adds a search for documents to this space.';
        }
    }


    public function getContentContainerConfigUrl(ContentContainerActiveRecord $container)
    {
        return $container->createUrl('/documentmanager/settings');
    }

    /**
     * @inheritdoc
     */
    public function getConfigUrl()
    {
        return Url::to([
            '/documentmanager/config'
        ]);
    }


}
