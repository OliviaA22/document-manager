<?php


namespace humhub\modules\documentmanager\models;

use Yii;

/**
 * This is the model class for table "cron_schedule".
 *
 * @property int $id
 * @property string $task_name
 * @property string|null $last_run
 * @property string|null $next_run
 */
class CronSchedule extends ActiveRecordExternal
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cron_schedule';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_name'], 'required'],
            [['last_run', 'next_run'], 'safe'],
            [['task_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_name' => 'Task Name',
            'last_run' => 'Last Run',
            'next_run' => 'Next Run',
        ];
    }
}
