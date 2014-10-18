<?php

class LogController extends Controller
{

    public function filters()
    {
        return [
            'accessControl',
        ];
    }

    public function accessRules()
    {
        return [
            ['allow', 'roles' => ['admin']],
            ['deny', 'users' => ['*']],
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new CActiveDataProvider('ActiveRecordLog', [
            'sort' => [
                'defaultOrder' => 'creationdate DESC, id DESC',
            ]
        ]);
        $this->render('list', ['dataProvider' => $dataProvider]);
    }
}