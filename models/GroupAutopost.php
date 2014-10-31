<?php

/**
 * @property integer $id
 * @property string $access_token
 * @property integer $page_id
 * @property integer $group_id
 * @property integer $hour
 * @property integer $status
 *
 * @property Group $group
 */
class GroupAutopost extends CActiveRecord
{
    static $hours = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];

    const STATUS_DISABLE = 0;
    const STATUS_ACTIVE = 1;

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
        return '{{group_autopost}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['access_token, page_id, group_id, hour', 'required'],
            ['page_id, group_id, hour', 'numerical', 'integerOnly' => true],
            ['access_token', 'length', 'max' => 255],
            ['hour', 'in', 'allowEmpty' => false, 'range' => self::$hours],
            ['status', 'in', 'allowEmpty' => true, 'range' => [self::STATUS_ACTIVE, self::STATUS_DISABLE]],
            ['id, access_token, page_id, group_id, hour', 'safe', 'on' => 'search'],
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
            'access_token' => 'Ключ',
            'page_id' => 'Страница',
            'group_id' => 'Группа',
            'hour' => 'Час',
        ];
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('access_token', $this->access_token, true);
        $criteria->compare('page_id', $this->page_id);
        $criteria->compare('group_id', $this->group_id);
        $criteria->compare('hour', $this->hour);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return GroupAutopost
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
