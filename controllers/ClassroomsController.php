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
        $classroms = new Classrooms();
        $classroms = $classroms->byName()->findAll();
        $model = new Classrooms;
        $this->render('list', ['classrooms' => $classroms, 'model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = new Classrooms();
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
                    $model->setAttributes(Yii::app()->request->getParam('Classrooms'));
                    if (!Yii::app()->user->checkAccess('admin')) {
                        $model->setAttribute('owner_id', Yii::app()->user->id);
                    }
                    if ($model->save()) {
                        Yii::app()->user->setFlash('success', 'Кабинет успешно сохранен');
                        $this->redirect(['index']);
                    }
                }
            } else
                throw new CHttpException(403, 'Нельзя редактировать чужой объект');
            $this->render('form', ['model' => $model, 'users_list' => $users_list]);
        } else
            throw new CHttpException(404, 'Кабинет не найден');
    }

    public function actionCreate()
    {
        $model = new Classrooms('insert');
        $users = new Users();
        $users = $users->findAll();
        $users_list = [];
        $users_list[''] = '-';
        foreach ($users as $user) {
            $users_list[$user->id] = $user->username;
        }
        if (Yii::app()->request->isPostRequest) {
            $classroom = Yii::app()->request->getParam('Classrooms');
            $model->setAttributes($classroom);
            if (!Yii::app()->user->checkAccess('admin')) {
                $model->setAttribute('owner_id', Yii::app()->user->id);
            }
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Кабинет успешно создан');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model, 'users_list' => $users_list]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = new Classrooms();
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
            throw new CHttpException(404, 'Кабинет не найден');
    }
}