<?php

class Controller extends CController
{
    /**
     * @var string
     */
    public $layout = '//layouts/blank';

    /**
     * @var array
     */
    public $menu = [];

    /**
     * @var array
     */
    public $breadcrumbs = [];

    /**
     * @param CAction $action
     */
    public function beforeAction($action)
    {
        if (Yii::app()->user->isGuest && ($action->id !== 'login' || $action->id !== 'error')) {
            Yii::app()->user->setReturnUrl($action->controller->getId() . '/' . $action->getId());
            $this->redirect(Yii::app()->createUrl('site/login'));
        }
        return true;
    }
}