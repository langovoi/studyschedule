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

    public function getCountUserNotifications()
    {
        $user = Users::model()->findByPk(Yii::app()->user->getId());
        if ($user) {
            $invites = GroupInvite::model()->count('email = :email AND status = 0', [':email' => $user->email]);
            return $invites;
        } else return 0;
    }
}