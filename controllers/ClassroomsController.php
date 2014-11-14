<?php

class ClassroomsController extends Controller
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
            ['allow', 'users' => ['@']],
            ['deny', 'users' => ['*']],
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new CActiveDataProvider('Classrooms', [
            'sort' => [
                'defaultOrder' => 'name ASC',
            ]
        ]);
        $model = new Classrooms('search');
        if (!Yii::app()->request->isAjaxRequest && !Yii::app()->request->getParam('ajax'))
            $this->render('list', ['dataProvider' => $dataProvider, 'model' => $model]);
        else {
            $model->setAttributes(Yii::app()->request->getParam('Classrooms'));
            $dataProvider = $model->search();
            $dataProvider->setSort([
                'defaultOrder' => 'name ASC',
            ]);
            $this->renderPartial('_list', ['dataProvider' => $dataProvider, 'model' => $model]);
        }
    }

    public function actionCreate()
    {
        $model = new Classrooms('insert');
        $users_list = CHtml::listData(Users::model()->findAll(), 'id', 'username');
        if (Yii::app()->request->isPostRequest) {
            $classroom = Yii::app()->request->getParam('Classrooms');
            $model->setAttributes($classroom);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Кабинет успешно создан');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model, 'users_list' => $users_list]);
    }

    public function actionUpdate($id)
    {
        $model = Classrooms::model()->findByPk($id);
        if (!$model)
            throw new CHttpException(404, 'Кабинет не найден');
        $users_list = CHtml::listData(Users::model()->findAll(), 'id', 'username');
        if (!Yii::app()->user->checkAccess('admin') && $model->owner_id !== Yii::app()->user->id)
            throw new CHttpException(403, 'Нельзя редактировать чужой объект');
        if (Yii::app()->request->isPostRequest) {
            $model->setAttributes(Yii::app()->request->getParam('Classrooms'));
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Кабинет успешно сохранен');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model, 'users_list' => $users_list]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = Classrooms::model()->findByPk($id);
        if (!$model)
            throw new CHttpException(404, 'Кабинет не найден');
        if (!Yii::app()->user->checkAccess('admin') && $model->owner_id != Yii::app()->user->id)
            throw new CHttpException(403, 'Нельзя удалить чужой объект');
        if ($confirm) {
            if ($model->delete()) {
                Yii::app()->user->setFlash('success', 'Кабинет успешно удален');
                $this->redirect(['index']);
            } else {
                Yii::app()->user->setFlash('error', 'Ошибка при удалении');
            }
        }
        $this->render('delete', ['model' => $model]);
    }
}