<?php

class UsersController extends Controller
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
        $users = new Users();
        $users = $users->findAll();
        $model = new Users;
        $this->render('list', ['users' => $users, 'model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = new Users();
        if ($model = $model->findByPk($id)) {
            if (Yii::app()->request->isPostRequest) {
                $password = $model->password;
                $model->setAttributes(Yii::app()->request->getParam('Users'));
                if (!$model->password) {
                    $model->password = $password;
                } else {
                    $model->password = md5($model->password);
                }
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', 'Пользователь успешно сохранен');
                    $this->redirect(['index']);
                }
            }
            $model->password = '';
            $this->render('form', ['model' => $model]);
        } else
            throw new CHttpException(404, 'Пользователь не найден');
    }

    public function actionCreate()
    {
        $model = new Users('insert');
        if (Yii::app()->request->isPostRequest) {
            $user = Yii::app()->request->getParam('Users');
            $model->setAttributes($user);
            $model->password = md5($model->password);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Пользователь успешно создан');
                Yii::app()->authManager->assign('user', $model->id);
                $this->redirect(['index']);
            } else {
                $model->password = $user['password'];
            }
        }
        $this->render('form', ['model' => $model]);
    }

    public function actionDelete($id, $confirm = 0)
    {
        if ($id != Yii::app()->user->getId()) {
            $model = new Users();
            if ($model = $model->findByPk($id)) {
                if ($confirm) {
                    foreach (Yii::app()->authManager->getAuthItems(2, $model->id) as $auth_item => $value) {
                        Yii::app()->authManager->revoke($auth_item, $model->id);
                    }
                    $model->delete();
                    $this->redirect(['index']);
                } else {
                    $this->render('delete', ['model' => $model]);
                }
            } else
                throw new CHttpException(404, 'Пользователь не найден');
        } else {
            Yii::app()->user->setFlash('error', 'Нельзя удалить самого себя!');
            $this->redirect(['index']);
        }
    }
}