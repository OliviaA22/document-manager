<?php

namespace humhub\modules\documentmanager\controllers;


use humhub\modules\documentmanager\models\SettingsForm;
use humhub\modules\content\components\ContentContainerController;
use Yii;


/**
 * SettingsController handles the configuration requests.
 */
class SettingsController extends ContentContainerController
{
    /**
     * Configuration action for super admins.
     *
     * @return string
     */
    public function actionIndex()
    {
        $form = new SettingsForm(['contentContainer' => $this->contentContainer]);

        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            $this->view->saved();
            
            $redirectRoute = $form->displayEventsAdmin 
            ? ['backend/index', 'cguid' => Yii::$app->getRequest()->getQueryParam('cguid')] 
            : ['frontend/index', 'cguid' => Yii::$app->getRequest()->getQueryParam('cguid')];
            
            return $this->redirect($redirectRoute);         
        }         
        return $this->render('_form', ['model' => $form]);
    }
}
