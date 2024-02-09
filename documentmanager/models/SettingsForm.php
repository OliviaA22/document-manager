<?php

namespace humhub\modules\documentmanager\models;

use Yii;
use yii\base\Model;
use humhub\modules\documentmanager\Module;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerSettingsManager;



/**
 * SettingsForm defines the configurable fields.
 */
class SettingsForm extends Model
{

    public $displayEvents;
    public $displayEventsAdmin;

    public ContentContainerActiveRecord $contentContainer;

    public function init()
    {
        parent::init();

        $this->loadBySettings();
    }


 /**
     * @return \yii\base\Module
  */
    public function getModule()
    {
        return Yii::$app->getModule('documentmanager');
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'displayEvents' => 'General Event',
            'displayEventsAdmin' => 'Admin Event',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['displayEvents', 'displayEventsAdmin'], 'boolean'],
        ];
    }


    public function loadBySettings()
    {
        $this->displayEvents = $this->getSettings()->get('displayEvents', false);
        
        $this->displayEventsAdmin = $this->getSettings()->get('displayEventsAdmin', false);
    }


    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $this->getSettings()->set('displayEvents', $this->displayEvents);
        $this->getSettings()->set('displayEventsAdmin', $this->displayEventsAdmin);

        return true;
    }

    private function getSettings(): ContentContainerSettingsManager
    {
        return $this->getModule()->settings->contentContainer($this->contentContainer);
    }
}
