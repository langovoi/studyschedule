<?php

/**
 * @property integer $id
 * @property integer $number
 * @property integer $owner_id
 *
 * @property Users $owner
 * @property ScheduleElement[] $schedule_elements
 * @property GroupReplace[] $replaces
 */
class Group extends CActiveRecord
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
        return '{{group}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['number, owner_id', 'numerical', 'integerOnly' => true],
            ['number', 'unique'],
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
            'schedule_elements' => [self::HAS_MANY, 'ScheduleElement', 'group_id'],
            'replaces' => [self::HAS_MANY, 'GroupReplace', 'group_id']
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

    /**
     * Данная функция фильтрует группы по наполненности расписания для текущего семестра
     *
     * @param bool $bool Параметр указыкает какие группы нужны. true - заполненные, false - не заполненные
     * @return Group
     */
    public function filled($bool = true)
    {
        $schedule_table = ScheduleElement::model()->tableName();
        $semester = Semesters::model()->actual();
        $criteria = $this->getDbCriteria();
        $criteria->params[':semester_id'] = $semester->id;
        for ($i = 1; $i <= 2; $i++)
            for ($j = 1; $j <= 6; $j++) {
                $sql = "(SELECT COUNT(*) FROM `$schedule_table` WHERE `$schedule_table`.`week_number` = $i AND `$schedule_table`.`week_day` = $j AND `$schedule_table`.`group_id` = `t`.`id` AND `$schedule_table`.`semester_id` = :semester_id) " . ($bool ? ">" : "=") . " 0";
                $criteria->addCondition($sql, $bool ? 'AND' : 'OR');
            }
        return $this;
    }
}
