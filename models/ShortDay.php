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
            ['date', 'dateCheck'],
            ['id, name, date', 'safe', 'on' => 'search'],
        ];
    }

    public function dateCheck($attribute)
    {
        /** @var Semesters $semester */
        $semester = Semesters::model()->actual();
        $time = strtotime($this->$attribute);
        if ($time < strtotime(date('Y-m-d'))) {
            $this->addError($attribute, 'Нельзя установить дату меньше сегоднешней');
            return false;
        }
        elseif(date('N', $time) == 7) {
            $this->addError($attribute, 'Нельзя установить дату на воскресенье');
            return false;
        }
        if ($time < strtotime($semester->start_date) || $time > strtotime($semester->end_date))
            $this->addError($attribute, 'Дата не может быть за пределами текущего семестра');
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
