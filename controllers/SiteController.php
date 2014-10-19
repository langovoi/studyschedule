<?php

class SiteController extends Controller
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
            ['allow', 'users' => ['*'], 'actions' => ['index', 'login', 'error', 'invite']],
            ['allow', 'users' => ['@'], 'actions' => ['logout', 'dashboard']],
            ['deny', 'users' => ['*']],
        ];
    }

    public function actionDashboard()
    {
        $groups = new Group();
        $groups = $groups->findAllByAttributes(['owner_id' => Yii::app()->user->getId()]);
        $groups_member = new GroupMember();
        $groups_member = $groups_member->findAllByAttributes(['user_id' => Yii::app()->user->getId()]);
        $this->render('dashboard', ['groups' => $groups, 'groups_member' => $groups_member]);
    }

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
                    $this->redirect($this->createUrl(Yii::app()->user->returnUrl && Yii::app()->user->returnUrl != '/' ? Yii::app()->user->returnUrl : 'site/index'));
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

    public function actionInvite($hash)
    {
        $invite = GroupInvite::model()->with('group')->findByAttributes(['status' => 0, 'hash' => $hash]);
        if (!$invite)
            throw new CHttpException(404, 'Данное приглашение не найдено или было отменено');
        $model = new Users();
        if (Yii::app()->request->isPostRequest) {
            $user = Yii::app()->request->getParam('Users');
            $model->setAttributes($user);
            $model->setAttributes([
                'email' => $invite->email,
                'password' => md5($user['password'])
            ]);
            if ($model->save()) {
                $user_identity = new UserIdentity($model->username, $model->password);
                $user_identity->authenticate();
                Yii::app()->user->login($user_identity, 60 * 60 * 24 * 7);
                $invite->setAttribute('status', GroupInvite::INVITE_ACCEPT);
                $invite->save();
                $group_member = new GroupMember();
                $group_member->setAttributes([
                    'group_id' => $invite->group_id,
                    'user_id' => $model->id
                ]);
                Yii::app()->authManager->assign('user', $model->id);
                $group_member->save();
                $this->redirect(['site/index']);
            }
        }
        $this->render('invite', ['model' => $model]);
    }
}