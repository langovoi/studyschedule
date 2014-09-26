<?php

class CallListsController extends Controller
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
        $call_lists = new CallLists();
        $model = new CallLists();
        $call_lists = $call_lists->findAll();
        $this->render('list', ['call_lists' => $call_lists, 'model' => $model]);
    }

    public function actionCreate()
    {
        $model = new CallLists('insert');
        if (Yii::app()->request->isPostRequest) {
            $call_list = Yii::app()->request->getParam('CallLists');
            $model->setAttributes($call_list);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Список успешно создан');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = new CallLists();
        if ($model = $model->findByPk($id)) {
            if (Yii::app()->request->isPostRequest) {
                $call_list = Yii::app()->request->getParam('CallLists');
                $model->setAttributes($call_list);
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', 'Список успешно сохранен');
                    $this->redirect(['index']);
                }
            }
        } else
            throw new CHttpException(404, 'Список звонков не найден');
        $this->render('form', ['model' => $model]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = new CallLists();
        if ($model = $model->findByPk($id)) {
            if ($confirm) {
                if ($model->delete()) {
                    $this->redirect(['index']);
                } else {
                    Yii::app()->user->setFlash('error', 'Список не удален');
                }
            } else {
                $this->render('delete', ['model' => $model]);
            }
        } else
            throw new CHttpException(404, 'Список звонков не найден');
    }
}