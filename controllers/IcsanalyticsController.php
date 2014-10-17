<?php

class IcsAnalyticsController extends Controller
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
        $count = IcsAnalytics::model()->count($criteria);
        $pages = new CPagination($count);
        $model = new IcsAnalytics();
        $criteria->order = 'time DESC, id DESC';
        $pages->pageSize = 10;
        $pages->applyLimit($criteria);
        $models = IcsAnalytics::model()->findAll($criteria);
        $this->render('list', ['models' => $models, 'model' => $model, 'pages' => $pages]);
    }
}