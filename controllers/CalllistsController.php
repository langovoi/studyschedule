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
        $dataProvider = new CActiveDataProvider('CallLists');
        $model = new CallLists('search');
        if (!Yii::app()->request->isAjaxRequest || !Yii::app()->request->getParam('ajax'))
            $this->render('list', ['dataProvider' => $dataProvider, 'model' => $model]);
        else {
            $model->setAttributes(Yii::app()->request->getParam('CallLists'));
            $dataProvider = $model->search();
            $this->renderPartial('_list', ['dataProvider' => $dataProvider, 'model' => $model]);
        }
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
        /** @var CallLists $model */
        $model = CallLists::model()->findByPk($id);
        if (!$model)
            throw new CHttpException(404, 'Список звонков не найден');
        if (Yii::app()->request->isPostRequest) {
            $call_list = Yii::app()->request->getParam('CallLists');
            $model->setAttributes($call_list);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Список успешно сохранен');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        /** @var CallLists $model */
        $model = CallLists::model()->findByPk($id);
        if (!$model)
            throw new CHttpException(404, 'Список звонков не найден');
        if (!$confirm) {
            $this->render('delete', ['model' => $model]);
        } else {
            if ($model->delete()) {
                $this->redirect(['index']);
            } else {
                Yii::app()->user->setFlash('error', 'Список не удален');
            }
        }
    }
}