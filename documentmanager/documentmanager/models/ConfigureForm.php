<?php

namespace humhub\modules\documentmanager\models;

use Yii;

/**
 * ConfigureForm defines the configurable fields.
 */
class ConfigureForm extends \yii\base\Model
{
    /**
     * @var string $class connection Class with full path.
     */
    public $class;

    /**
     * @var string $driverName driver name.
     */
    public $driverName;

    /**
     * @var string $dsn Data source name.
     */
    public $dsn;

    /**
     * @var string $username username of the database user.
     */
    public $username;

    /**
     * @var string $password password of the database user.
     */
    public $password;

    /**
     * @var string $charset charset to use.
     */
    public $charset;

    /**
     * @var string $strtotimeString this value is used for cron scheduling. This value MUST be in format "next [weekday] [time]". For more details see: https://www.php.net/manual/en/function.strtotime.php
     */
    public $strtotimeString;

    /**
     * @var string $weekday displays days of the week for users to select
     * 
     */
    public $weekday;

    /**
     * @var string $time displays time for users to select. It will be combined with the $weekday variable to set the $strtotimeString
     * 
     */
    public $time;

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            [['class', "driverName", "dsn", "username", "password", "weekday", "time"], 'required'],
            [['charset', 'strtotimeString'], 'safe'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels()
    {
        return [
            'class' => 'class',
            'driverName' => 'driverName',
            'dsn' => 'dsn',
            'username' => 'username',
            'password' => 'password',
            'charset' => 'charset',
            'strtotimeString' => 'Cron Job Configuration',
            'time' => 'time',
            'weekday' => 'weekday',
        ];
    }

    public function attributeHints()
    {
        return [
        ];
    }

    /**
     * Retrieve the $strtotimeString value so that it can be used to calculate 
     * the next run time when creating a new notification entry in the database.
     * 
     * @return string The scheduled time in 'Y-m-d H:i:s' format.
     */
    public function getNextRunTime()
    {
        $manager = Yii::$app->getModule('documentmanager')->settings;
        $nextRunTime = strtotime('next ' . $manager->get('strtotimeString'));
        return date('Y-m-d H:i:s', $nextRunTime);
    }


}
