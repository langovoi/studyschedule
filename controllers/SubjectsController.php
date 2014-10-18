<?php

class SubjectsController extends Controller
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
        $subjects = new Subjects();
        $subjects = $subjects->findAll();
        $model = new Subjects();
        $this->render('list', ['subjects' => $subjects, 'model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = new Subjects();
        $users_list = CHtml::listData(Users::model()->findAll(), 'id', 'username');
        if ($model = $model->findByPk($id)) {
            if (Yii::app()->user->checkAccess('admin') || $model->owner_id == Yii::app()->user->id) {
                if (Yii::app()->request->isPostRequest) {
                    $model->setAttributes(Yii::app()->request->getParam('Subjects'));
                    if (!Yii::app()->user->checkAccess('admin')) {
                        $model->setAttribute('owner_id', Yii::app()->user->id);
                    }
                    if ($model->save()) {
                        Yii::app()->user->setFlash('success', 'Предмет успешно сохранен');
                        $this->redirect(['index']);
                    }
                }
            } else
                throw new CHttpException(403, 'Нельзя редактировать чужой объект');
            $this->render('form', ['model' => $model, 'users_list' => $users_list]);
        } else
            throw new CHttpException(404, 'Предмет не найден');
    }

    public function actionCreate()
    {
        $model = new Subjects('insert');
        $users_list = CHtml::listData(Users::model()->findAll(), 'id', 'username');
        if (Yii::app()->request->isPostRequest) {
            $classroom = Yii::app()->request->getParam('Subjects');
            $model->setAttributes($classroom);
            if (!Yii::app()->user->checkAccess('admin')) {
                $model->setAttribute('owner_id', Yii::app()->user->id);
            }
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Предмет успешно создан');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model, 'users_list' => $users_list]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = new Subjects();
        if ($model = $model->findByPk($id)) {
            if (Yii::app()->user->checkAccess('admin') || $model->owner_id == Yii::app()->user->id) {
                if ($confirm) {
                    $model->delete();
                    $this->redirect(['index']);
                } else {
                    $this->render('delete', ['model' => $model]);
                }
            } else
                throw new CHttpException(403, 'Нельзя удалить чужой объект');
        } else
            throw new CHttpException(404, 'Предмет не найден');
    }
}