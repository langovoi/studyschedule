<?php

class FeedbackForm extends CFormModel
{
    public $name;
    public $subject;
    public $text;
    public $email;
    public $captcha;

    public function rules()
    {
        return [
            ['subject, text, email, name', 'required'],
            ['email', 'email'],
            ['subject, name', 'length', 'max' => 255],
            ['captcha', 'captcha', 'captchaAction' => 'feedback/captcha']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Имя',
            'email' => 'E-mail',
            'subject' => 'Тема',
            'text' => 'Сообщение',
            'captcha' => 'Код проверки',
        ];
    }
}