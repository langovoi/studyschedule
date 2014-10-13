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

    public function actionIndex($page = 1)
    {
        $logs = new ActiveRecordLog();
        $model = new ActiveRecordLog();
        $count = $logs->count();
        if (($page - 1) * 10 < $count || $page == 1) {
            $logs = $logs->findAll(['order' => 'creationdate DESC, id DESC', 'limit' => 10, 'offset' => ($page - 1) * 10]);
            $this->render('list', ['logs' => $logs, 'model' => $model, 'pages' => floor($count / 10), 'page' => $page]);
        } else
            throw new CHttpException(404, 'Такой страницы нет :(');
    }
}