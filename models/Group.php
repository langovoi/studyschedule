<?php

/**
 * @property integer $id
 * @property integer $number
 * @property integer $owner_id
 *
 * @property Users $owner
 */
class Group extends CActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{group}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['number, owner_id', 'numerical', 'integerOnly' => true],
            ['id, number, owner_id', 'safe', 'on' => 'search'],
        ];
    }

    /**
     * @return array
     */
    public function relations()
    {
        return [
            'owner' => [self::BELONGS_TO, 'Users', 'owner_id'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'number' => 'Номер группы',
            'owner_id' => 'Владелец',
        ];
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('number', $this->number);
        $criteria->compare('owner_id', $this->owner_id);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return Group
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
