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
        $dataProvider = new CActiveDataProvider('Users');
        $model = new Users('search');
        if (!Yii::app()->request->isAjaxRequest && !Yii::app()->request->getParam('ajax'))
            $this->render('list', ['dataProvider' => $dataProvider, 'model' => $model]);
        else {
            $model->setAttributes(Yii::app()->request->getParam('Users'));
            $dataProvider = $model->search();
            $this->renderPartial('_list', ['dataProvider' => $dataProvider, 'model' => $model]);
        }
    }

    public function actionUpdate($id)
    {
        $model = new Users();
        $auth_manager = Yii::app()->authManager;
        $auth_items = array_keys($auth_manager->getAuthItems());
        if ($model = $model->findByPk($id)) {
            if (Yii::app()->request->isPostRequest) {
                $password = $model->password;
                $user = Yii::app()->request->getParam('Users');
                $model->setAttributes($user);
                if (!$model->password) {
                    $model->password = $password;
                } else {
                    $model->password = md5($model->password);
                }
                if ($model->save()) {
                    foreach (Yii::app()->authManager->getAuthItems(2, $model->id) as $auth_item => $value) {
                        Yii::app()->authManager->revoke($auth_item, $model->id);
                    }
                    Yii::app()->authManager->assign(isset($auth_items[$user['rights']]) ? $auth_items[$user['rights']] : 'user', $model->id);
                    Yii::app()->user->setFlash('success', 'Пользователь успешно сохранен');
                    $this->redirect(['index']);
                }
            }
            $model->password = '';
            $this->render('form', ['model' => $model, 'auth_items' => $auth_items, 'user_right' => array_search(array_keys($auth_manager->getAuthAssignments($model->id))[0], $auth_items)]);
        } else
            throw new CHttpException(404, 'Пользователь не найден');
    }

    public function actionCreate()
    {
        $model = new Users('insert');
        $auth_manager = Yii::app()->authManager;
        $auth_items = array_keys($auth_manager->getAuthItems());
        if (Yii::app()->request->isPostRequest) {
            $user = Yii::app()->request->getParam('Users');
            $model->setAttributes($user);
            $model->password = md5($model->password);
            if ($model->save()) {
                Yii::app()->user->setFlash('success', 'Пользователь успешно создан');
                Yii::app()->authManager->assign(isset($auth_items[$user['rights']]) ? $auth_items[$user['rights']] : 'user', $model->id);
                $this->redirect(['index']);
            } else {
                $model->password = $user['password'];
            }
        }
        $this->render('form', ['model' => $model, 'auth_items' => $auth_items]);
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