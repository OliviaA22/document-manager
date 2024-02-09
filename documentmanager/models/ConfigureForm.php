<?php

namespace humhub\modules\documentmanager\models;

use Yii;

/**
 * ConfigureForm defines the configurable fields.
 */
class ConfigureForm extends \yii\base\Model {
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
     * {@inheritDoc}
     */
    public function rules() {
        return [
            [['class', "driverName", "dsn", "username", "password"], 'required'],
            [['charset'], 'safe'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels() {
        return [
            'class' => 'class',
            'driverName' => 'driverName',
            'dsn' => 'dsn',
            'username' => 'username',
            'password' => 'password',
            'charset' => 'charset',
        ];
    }
}
