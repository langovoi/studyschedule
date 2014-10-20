<?php

/**
 * @property integer $id
 * @property integer $group_number
 * @property string $name
 * @property string $email
 * @property string $text
 * @property string $time
 * @property string $hash
 * @property integer $status
 */
class Invite extends CActiveRecord
{
    public function behaviors()
    {
        return [
            'ActiveRecordLogableBehavior' =>
                'application.behaviors.ActiveRecordLogableBehavior',
        ];
    }

    public $captcha;

    const INVITE_ACCEPT = 1;
    const INVITE_CREATE = 0;
    const INVITE_USED = 2;
    const INVITE_DECLINE = -1;

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{invite}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['group_number, text, email, name', 'required'],
            ['name, email', 'length', 'max' => 255],
            ['group_number, status', 'numerical', 'integerOnly' => true],
            ['group_number', 'groupCheck'],
            ['email', 'email'],
            ['email', 'emailCheck'],
            ['captcha', 'captcha', 'captchaAction' => 'help/captcha', 'on' => 'insert'],
            ['time, hash', 'safe'],
            ['id, group_number, name, email, text, status', 'safe', 'on' => 'search'],
        ];
    }

    public function emailCheck($attribute)
    {
        if (Users::model()->findByAttributes(['email' => $this->$attribute]))
            $this->addError($attribute, 'Данная почта уже занята в системе');
    }

    public function beforeSave()
    {
        if ($this->getScenario() == 'insert') {
            $this->setAttribute('time', date("Y-m-d H:i:s"));
            $this->setAttribute('hash', md5($this->email . $this->group_number . uniqid()));
        }
        return parent::beforeSave();
    }

    public function groupCheck($attribute)
    {
        if (Group::model()->findByAttributes(['number' => $this->$attribute]))
            $this->addError($attribute, 'Данная группа уже есть в системе');
    }

    /**
     * @return array
     */
    public function relations()
    {
        return [];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_number' => 'Номер группы',
            'name' => 'Имя',
            'email' => 'E-mail',
            'text' => 'Почему мы должны одобрить заявку',
            'status' => 'Статус',
            'captcha' => 'Код проверки',
        ];
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('group_number', $this->group_number);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('text', $this->text, true);
        $criteria->compare('status', $this->status);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return Invite
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
