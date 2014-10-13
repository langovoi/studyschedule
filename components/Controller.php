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

    public function getCurrentWeekNumber()
    {
        $semester = Semesters::model()->byStartDate()->find();
        $week_number = (date('W') - date('W', strtotime($semester->start_date))) % ($semester->week_number + 1) + 1;
        return $week_number;
    }
}