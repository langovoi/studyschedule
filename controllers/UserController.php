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

    public function actionNotifications()
    {
        $user = Users::model()->findByPk(Yii::app()->user->getId());
        $invites = GroupInvite::model()->with('group')->findAll('email = :email AND status = 0', [':email' => $user->email]);

        $this->render('notifications', ['invites' => $invites]);
    }

    public function actionInvite($invite_id, $accept)
    {
        $invite = GroupInvite::model()->findByPk($invite_id);
        if (!$invite)
            throw new CHttpException(404);
        $invite->setAttribute('status', ($accept == 1 ? 1 : 2));
        if ($accept == 1) {
            $group_member = new GroupMember();
            $group_member->setAttributes([
                'group_id' => $invite->group_id,
                'user_id' => Yii::app()->user->getId()
            ]);
            $group_member->save();
        }
        if ($invite->save()) {
            Yii::app()->user->setFlash('success', 'Приглашение ' . ($accept == 1 ? 'принято' : 'отклонено'));
        } else Yii::app()->user->setFlash('error', 'Произошла ошибка');
        $this->redirect(['notifications']);
    }
}