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
        if (!Yii::app()->request->isAjaxRequest || !Yii::app()->request->getParam('ajax'))
            $this->render('list', ['dataProvider' => $dataProvider, 'model' => $model]);
        else {
            $model->setAttributes(Yii::app()->request->getParam('Invite'));
            $dataProvider = $model->search();
            $this->renderPartial('_list', ['dataProvider' => $dataProvider, 'model' => $model]);
        }
    }

    public function actionAccept($id)
    {
        if (!($model = Invite::model()->findByPk($id)))
            throw new CHttpException(404);
        $mail = new YiiMailer();
        $mail->setView('invite/accept');
        $mail->setData(['model' => $model]);
        $mail->setFrom('marklangovoi@gmail.com', 'Система управления учебным расписанием');
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
        if (!($model = Invite::model()->findByPk($id)))
            throw new CHttpException(404);
        $mail = new YiiMailer();
        $mail->setView('invite/decline');
        $mail->setFrom('marklangovoi@gmail.com', 'Система управления учебным расписанием');
        $mail->setTo($model->email);
        $mail->setSubject('Заявка на создание');
        $mail->send();
        if ($mail->send()) {
            $model->status = Invite::INVITE_DECLINE;
            if ($model->save())
                Yii::app()->user->setFlash('success', 'Заявка успешно отклонена');
            else
                Yii::app()->user->setFlash('error', 'Ошибка при смене статуса');
        } else
            Yii::app()->user->setFlash('error', 'Ошибка при отправке письма');
        $this->redirect(['index']);
    }
}