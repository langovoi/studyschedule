<?php

class FeedbackController extends Controller
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
            ['allow', 'users' => ['*']]
        ];
    }

    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'CCaptchaAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $form = new FeedbackForm();
        if (Yii::app()->request->isPostRequest) {
            $params = Yii::app()->request->getParam('FeedbackForm');
            $form->setAttributes($params);
            if ($form->validate()) {
                $mail = new YiiMailer();
                $mail->setView('feedback');
                $mail->setData(['form' => $form]);
                $mail->setFFix rom($form->email, $form->name);
                $mail->setReplyTo($form->email);
                $mail->setTo(Yii::app()->params->adminEmail);
                $mail->setSubject('Система расписания: ' . $form->subject);
                if ($mail->send()) {
                    Yii::app()->user->setFlash('success', 'Ваше сообщение отправлено, спасибо!');
                    $form->unsetAttributes();
                } else {
                    Yii::app()->user->setFlash('error', 'Ошибка при отправке');
                }
            }
        }
        $this->render('index', ['model' => $form]);
    }
}