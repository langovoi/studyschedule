<?php

/**
 * @property integer $id
 * @property integer $group_id
 * @property integer $user_id
 *
 * @property Users $user
 * @property Group $group
 */
class GroupMember extends CActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{group_member}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['group_id, user_id', 'required'],
            ['group_id, user_id', 'numerical', 'integerOnly' => true],
            ['id, group_id, user_id', 'safe', 'on' => 'search'],
        ];
    }

    /**
     * @return array
     */
    public function relations()
    {
        return [
            'user' => [self::BELONGS_TO, 'Users', 'user_id'],
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
            'user_id' => 'Пользователь',
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
        $criteria->compare('user_id', $this->user_id);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return GroupMember
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
