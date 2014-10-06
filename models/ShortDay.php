<?php

/**
 * @property integer $id
 * @property string $name
 * @property string $date
 */
class ShortDay extends CActiveRecord
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
        return '{{short_day}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['name, date', 'required'],
            ['date', 'unique'],
            ['id, name, date', 'safe', 'on' => 'search'],
        ];
    }

    /**
     * @return array
     */
    public function relations()
    {
        return [
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'date' => 'Дата',
        ];
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('date', $this->date, true);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return ShortDay
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
