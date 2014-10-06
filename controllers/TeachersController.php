<?php

class TeachersController extends Controller
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
        $teachers = new Teachers();
        $teachers = $teachers->findAll();
        $model = new Teachers;
        $this->render('list', ['teachers' => $teachers, 'model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = new Teachers();
        $users = new Users();
        $users = $users->findAll();
        $users_list = [];
        $users_list[''] = '-';
        foreach ($users as $user) {
            $users_list[$user->id] = $user->username;
        }
        if ($model = $model->findByPk($id)) {
            if (Yii::app()->user->checkAccess('admin') || $model->owner_id == Yii::app()->user->id) {
                if (Yii::app()->request->isPostRequest) {
                    $model->setAttributes(Yii::app()->request->getParam('Teachers'));
                    $model->setAttribute('owner_id', Yii::app()->user->id);
                    if ($model->save()) {
                        Yii::app()->user->setFlash('success', 'Преподаватель успешно сохранен');
                        $this->redirect(['index']);
                    }
                }
            } else
                throw new CHttpException(403, 'Нельзя редактировать чужой объект');
            $this->render('form', ['model' => $model, 'users_list' => $users_list]);
        } else
            throw new CHttpException(404, 'Преподаватель не найден');
    }

    public function actionCreate()
    {
        $model = new Teachers('insert');
        $users = new Users();
        $users = $users->findAll();
        $users_list = [];
        $users_list[''] = '-';
        foreach ($users as $user) {
            $users_list[$user->id] = $user->username;
        }
        if (Yii::app()->request->isPostRequest) {
            $classroom = Yii::app()->request->getParam('Teachers');
            $model->setAttributes($classroom);
            if (Yii::app()->user->checkAccess('user')) {
                $model->setAttribute('owner_id', Yii::app()->user->id);
            }
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Преподаватель успешно создан');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model, 'users_list' => $users_list]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = new Teachers();
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
            throw new CHttpException(404, 'Преподаватель не найден');
    }
}