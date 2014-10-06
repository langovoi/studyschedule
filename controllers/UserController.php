<?php

class UserController extends Controller
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

    public function actionChangePassword()
    {
        $model = new Users();
        if (Yii::app()->request->isPostRequest) {
            $user = Yii::app()->request->getParam('Users');
            $model = $model->findByPk(Yii::app()->user->getId());
            $model->setScenario('change_password');
            $model->setAttributes($user);
            if ($model->validate()) {
                $model->password = md5($model->new_password);
                if ($model->save(false))
                    Yii::app()->user->setFlash('success', 'Пароль изменен успешно');
                else Yii::app()->user->setFlash('error', 'Произошла странная ошибка...');
                $this->redirect(['changepassword']);
            }
        }
        $this->render('change_password', ['model' => $model]);
    }
}