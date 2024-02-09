<?php

namespace humhub\modules\documentmanager\controllers;

use Yii;
use humhub\modules\documentmanager\models\ConfigureForm;
use yii\helpers\Url;

/**
 * ConfigController handles the configuration requests.
 */
class ConfigController extends \humhub\modules\admin\components\Controller {
    /**
     * Configuration action for super admins.
     *
     * @return string
     */
    public function actionIndex() {
        $form = new ConfigureForm();
        $manager = Yii::$app->getModule('documentmanager')->settings;
        $form->class = $manager->get('class');
        $form->driverName = $manager->get('driverName');
        $form->dsn = $manager->get('dsn');
        $form->username = $manager->get('username');
        $form->password = $manager->get('password');
        $form->charset = $manager->get('charset');
        
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $manager->set('class', $form->class);
            $manager->set('driverName', $form->driverName);
            $manager->set('dsn', $form->dsn);
            $manager->set('username', $form->username);
            $manager->set('password', $form->password);
            $manager->set('charset', $form->charset);
            Yii::$app->getSession()->setFlash('view-status', [
                'success' => Yii::t('base', 'Saved'),
            ]);
            return $this->redirect(['/documentmanager/config'])->send();
        }
        
        return $this->render('index', [
            'model' => $form
        ]);
    }
}