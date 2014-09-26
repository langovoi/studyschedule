<?php

class SiteController extends Controller
{

    public function actionIndex()
    {
        $this->render('index');
    }

    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            $this->render('error', $error);
        }
    }

    public function actionLogin()
    {
        if (Yii::app()->user->isGuest) {
            $model = new Users('login');
            if (Yii::app()->request->isPostRequest) {
                $user = Yii::app()->request->getParam('Users');
                $model->setAttributes($user);
                $user_identity = new UserIdentity($model->username, md5($model->password));
                if ($model->validate() && $user_identity->authenticate()) {
                    Yii::app()->user->login($user_identity, 60 * 60 * 24 * 7); // sign-in for week
                    $this->redirect($this->createUrl(Yii::app()->user->returnUrl ? Yii::app()->user->returnUrl : '/'));
                } else {
                    $this->render('login', ['model' => $model, 'error' => $user_identity->errorCode]);
                }
            } else {
                $this->render('login', ['model' => $model]);
            }
        } else
            throw new CHttpException(403);
    }

    public function actionLogout()
    {
        if (!Yii::app()->user->isGuest) {
            Yii::app()->user->logout();
            $this->redirect('/');
        } else
            throw new CHttpException(403);
    }
}