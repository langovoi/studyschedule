<?php

class GroupInviteController extends Controller
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
        $dataProvider = new CActiveDataProvider('Invite', [
            'sort' => [
                'defaultOrder' => 'time DESC',
            ]
        ]);
        $model = new Invite('search');
        if (!Yii::app()->request->isAjaxRequest && !Yii::app()->request->getParam('ajax'))
            $this->render('list', ['dataProvider' => $dataProvider, 'model' => $model]);
        else {
            $this->renderPartial('_list', ['dataProvider' => $dataProvider, 'model' => $model]);
        }
    }

    public function actionAccept($id)
    {
        if (!($model = Invite::model()->findByPk($id)) || $model->status != Invite::INVITE_CREATE)
            throw new CHttpException(404);
        $mail = new YiiMailer();
        $mail->setView('invite/accept');
        $mail->setData(['model' => $model]);
        $mail->setFrom(isset(Yii::app()->params->YiiMailer->Username) ? Yii::app()->params->YiiMailer->Username : Yii::app()->params->adminEmail, 'Система управления учебным расписанием');
        $mail->setTo($model->email);
        $mail->setSubject('Заявка на создание');
        if ($mail->send()) {
            $model->status = Invite::INVITE_ACCEPT;
            if ($model->save())
                Yii::app()->user->setFlash('success', 'Заявка успешно обработана');
            else
                Yii::app()->user->setFlash('error', 'Ошибка при смене статуса');
        } else
            Yii::app()->user->setFlash('error', 'Ошибка при отправке письма');
        $this->redirect(['index']);
    }

    public function actionDecline($id)
    {
        if (!($model = Invite::model()->findByPk($id)) || !in_array($model->status, [Invite::INVITE_CREATE, Invite::INVITE_ACCEPT]))
            throw new CHttpException(404);
        if ($model->status == Invite::INVITE_CREATE) {
            $mail = new YiiMailer();
            $mail->setView('invite/decline');
            $mail->setFrom(isset(Yii::app()->params->YiiMailer->Username) ? Yii::app()->params->YiiMailer->Username : Yii::app()->params->adminEmail, 'Система управления учебным расписанием');
            $mail->setTo($model->email);
            $mail->setSubject('Заявка на создание');
            $mail->send();
            if (!$mail->send()) {
                Yii::app()->user->setFlash('error', 'Ошибка при отправке письма');
                $this->redirect(['index']);
            }
        }
        $model->status = Invite::INVITE_DECLINE;
        if ($model->save())
            Yii::app()->user->setFlash('success', 'Заявка успешно отклонена');
        else {
            Yii::app()->user->setFlash('error', 'Ошибка при смене статуса');
        }
        $this->redirect(['index']);
    }
}