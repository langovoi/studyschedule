<?php

/**
 * @property integer $id
 * @property integer $group_id
 * @property string $email
 * @property integer $status
 *
 * @property Group $group
 */
class GroupInvite extends CActiveRecord
{
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
            ['status', 'in', 'allowEmpty' => true, 'range' => [self::INVITE_CREATE, self::INVITE_ACCEPT, self::INVITE_CANCELED]],
            ['id, group_id, email, status', 'safe', 'on' => 'search'],
        ];
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
