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

    public function actionIndex($page = 1)
    {
        $logs = new IcsAnalytics();
        $model = new IcsAnalytics();
        $count = $logs->count();
        if (($page - 1) * 10 < $count || $page == 1) {
            $logs = $logs->findAll(['order' => 'time DESC, id DESC', 'limit' => 10, 'offset' => ($page - 1) * 10]);
            $this->render('list', ['logs' => $logs, 'model' => $model, 'pages' => ceil($count / 10), 'page' => $page]);
        } else
            throw new CHttpException(404, 'Такой страницы нет :(');
    }
}