<?php

/**
 * @property integer $id
 * @property integer $group_id
 * @property string $email
 * @property string $hash
 * @property integer $status
 *
 * @property Group $group
 */
class GroupInvite extends CActiveRecord
{
    public function behaviors()
    {
        return [
            'ActiveRecordLogableBehavior' =>
                'application.behaviors.ActiveRecordLogableBehavior',
        ];
    }
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{group_invite}}';
    }

    const INVITE_CREATE = 0;
    const INVITE_ACCEPT = 1;
    const INVITE_CANCELED = 2;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['group_id, email', 'required'],
            ['group_id, status', 'numerical', 'integerOnly' => true],
            ['email', 'length', 'max' => 255],
            ['email', 'email'],
            ['email', 'checkEmail', 'on' => 'insert'],
            ['status', 'in', 'allowEmpty' => true, 'range' => [self::INVITE_CREATE, self::INVITE_ACCEPT, self::INVITE_CANCELED]],
            ['hash', 'safe'],
            ['id, group_id, email, status', 'safe', 'on' => 'search'],
        ];
    }

    public function checkEmail($attribute)
    {
        $value = $this->$attribute;
        $user = Users::model()->findByAttributes(['email' => $value]);
        /*if (!$user) {
            $this->addError($attribute, 'В системе нет пользователя с данной почтой');
            return false;
        }*/
        if ($user) {
            $group_member = GroupMember::model()->findByAttributes(['group_id' => $this->group_id, 'user_id' => $user->id]);
            if ($group_member) {
                $this->addError($attribute, 'Данный пользователь уже член вашей группы');
                return false;
            }
            $group = Group::model()->findByPk($this->group_id);
            if ($group->owner_id == $user->id) {
                $this->addError($attribute, 'Администратор группы не может быть членом группы');
                return false;
            }
        }
        $active_invites = GroupInvite::model()->findByAttributes(['group_id' => $this->group_id, 'email' => $value, 'status' => self::INVITE_CREATE]);
        if ($active_invites)
            $this->addError($attribute, 'Данный пользователь уже имеет приглашение');
    }

    public function afterSave()
    {
        if ($this->getScenario() == 'insert') {
            $user = Users::model()->findByAttributes(['email' => $this->email]);
            if (!$user) {
                $group = Group::model()->findByPk($this->group_id);
                $mail = new YiiMailer();
                $mail->setView('invite');
                $mail->setData(array('group' => $group, 'hash' => $this->hash));
                $mail->setFrom('marklangovoi@gmail.com', 'Система управления учебным расписанием');
                $mail->setTo($this->email);
                $mail->setSubject('Приглашение');
                $mail->send();
            }
        }
    }


    public function beforeSave()
    {
        if ($this->getScenario() == 'insert') {
            $this->hash = md5($this->email . $this->group_id);
        }
        return parent::beforeSave();
    }

    /**
     * @return array
     */
    public function relations()
    {
        return [
            'group' => [self::BELONGS_TO, 'Group', 'group_id'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Группа',
            'email' => 'Почта',
            'status' => 'Статус',
        ];
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('group_id', $this->group_id);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('status', $this->status);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return GroupInvite
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
