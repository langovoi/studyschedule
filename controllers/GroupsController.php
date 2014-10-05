<?php

class GroupsController extends Controller
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
        $teachers = new Group();
        $teachers = $teachers->findAll();
        $model = new Group;
        $this->render('list', ['groups' => $teachers, 'model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = new Group();
        $users = new Users();
        $users = $users->findAll();
        $users_list = [];
        $users_list[''] = '-';
        foreach ($users as $user) {
            $users_list[$user->id] = $user->username;
        }
        if ($model = $model->findByPk($id)) {
            if (Yii::app()->request->isPostRequest) {
                $model->setAttributes(Yii::app()->request->getParam('Group'));
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', 'Группа успешно сохранен');
                    $this->redirect(['index']);
                }
            }
            $this->render('form', ['model' => $model, 'users_list' => $users_list]);
        } else
            throw new CHttpException(404, 'Преподаватель не найден');
    }

    public function actionCreate()
    {
        $model = new Group('insert');
        $users = new Users();
        $users = $users->findAll();
        $users_list = [];
        $users_list[''] = '-';
        foreach ($users as $user) {
            $users_list[$user->id] = $user->username;
        }
        if (Yii::app()->request->isPostRequest) {
            $group = Yii::app()->request->getParam('Group');
            $model->setAttributes($group);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Группа успешно создан');
                $this->redirect(['index']);
            }
        }
        $this->render('form', ['model' => $model, 'users_list' => $users_list]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        $model = new Group();
        if ($model = $model->findByPk($id)) {
            if ($confirm) {
                $model->delete();
                $this->redirect(['index']);
            } else {
                $this->render('delete', ['model' => $model]);
            }
        } else
            throw new CHttpException(404, 'Группа не найден');
    }
}