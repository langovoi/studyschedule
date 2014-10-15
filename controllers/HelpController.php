<?php

class HelpController extends Controller
{
    static $os = ['ios' => 'iOS', 'android' => 'Android'];

    public function filters()
    {
        return [
            'accessControl',
        ];
    }

    public function accessRules()
    {
        return [
            ['allow', 'users' => ['*']],
        ];
    }

    public function actionIndex()
    {
        $group_list = [];
        foreach (Group::model()->findAll() as $group)
            $group_list[$group->number] = $group->number;
        $this->render('index', ['group_list' => $group_list, 'os_list' => self::$os]);
    }

    public function actionPhone($group, $os)
    {
        $group = Group::model()->findByAttributes(['number' => $group]);
        if (!in_array(strtolower($os), array_keys(self::$os)))
            throw new CHttpException(404, "Инструкции для данной системы нет");
        if (!$group)
            throw new CHttpException(404, "Данной группы не найдено");
        $this->render('phone/' . $os, ['group' => $group]);

    }
}