<?php

/**
 *
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property array $groups
 */
class Users extends CActiveRecord
{
    public function behaviors()
    {
        return [
            'ActiveRecordLogableBehavior' =>
                'application.behaviors.ActiveRecordLogableBehavior',
        ];
    }

    public $old_password;
    public $new_password;
    public $repeat_password;
    public $groups = [];

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{users}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['username, email, password', 'required', 'except' => 'login, change_password'],
            ['username, email', 'unique', 'except' => 'login, change_password'],
            ['email', 'email', 'except' => 'login,change_password'],
            ['username', 'length', 'max' => 120],
            ['username', 'unique', 'except' => 'login,change_password'],
            ['username', 'match', 'pattern' => '/^[A-Za-z0-9]+$/', 'message' => 'Логин может состоять только из латиницы и цифр'],
            ['email', 'length', 'max' => 255],
            ['password', 'length', 'max' => 32],
            ['id, username, email', 'safe', 'on' => 'search'],
            ['username, password', 'required', 'on' => 'login'],
            ['old_password, new_password, repeat_password', 'required', 'on' => 'change_password'],
            ['old_password', 'findPasswords', 'on' => 'change_password'],
            ['repeat_password', 'compare', 'compareAttribute' => 'new_password', 'on' => 'change_password'],
        ];
    }

    public function findPasswords($attribute, $params)
    {
        $user = new Users;
        $user = $user->findByPk(Yii::app()->user->getId());
        if (strtolower($user->password) != strtolower(md5($this->old_password)))
            $this->addError($attribute, 'Старый пароль неверный');
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
            'username' => 'Логин',
            'email' => 'Почта',
            'password' => 'Пароль',
            'old_password' => 'Старый пароль',
            'new_password' => 'Новый пароль',
            'repeat_password' => 'Повторите пароль',
            'groups' => 'Группа',
        ];
    }

    public function afterFind()
    {
        $this->groups = Yii::app()->authManager->getAuthAssignments($this->id);

        return parent::afterFind();
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('username', $this->username, true);
        $criteria->compare('email', $this->email, true);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className active record class name.
     * @return Users the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
