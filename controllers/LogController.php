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
        $criteria = new CDbCriteria();
        $count = ActiveRecordLog::model()->count($criteria);
        $pages = new CPagination($count);
        $model = new ActiveRecordLog();
        $criteria->order = 'creationdate DESC, id DESC';
        $pages->pageSize = 10;
        $pages->applyLimit($criteria);
        $models = ActiveRecordLog::model()->findAll($criteria);
        $this->render('list', ['models' => $models, 'model' => $model, 'pages' => $pages]);
    }
}