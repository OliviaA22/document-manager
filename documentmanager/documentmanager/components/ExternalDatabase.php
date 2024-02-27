<?php

namespace humhub\modules\documentmanager\components;

use Yii;
use yii\base\BaseObject;

/**
 * Helper Class for managing files.
 */
class ExternalDatabase extends BaseObject {
    public static $connection = null;

    /**
     * 
     * @return \yii\db\Connection the database connection.
     */
    public static function getConnection() {
        if (self::$connection == null) {
            $manager = Yii::$app->getModule('documentmanager')->settings;
            $config = [
                'driverName' => $manager->get('driverName'),
                'dsn' => $manager->get('dsn'),
                'username' => $manager->get('username'),
                'password' => $manager->get('password'),
            ];
            $charset = $manager->get('charset');
            if ($charset) {
                $config['charset'] = $charset;
            }
            self::$connection = new \yii\db\Connection($config);
        }
        return self::$connection;
    }
}
