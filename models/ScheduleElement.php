<?php

/**
 * @property integer $id
 * @property integer $group_id
 * @property integer $semester_id
 * @property integer $week_number
 * @property integer $week_day
 * @property integer $number
 * @property integer $classroom_id
 * @property integer $teacher_id
 * @property integer $subject_id
 *
 * @property Subjects $subject
 * @property Classrooms $classroom
 * @property Group $group
 * @property Semesters $semester
 * @property Teachers $teacher
 */
class ScheduleElement extends CActiveRecord
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
        return '{{schedule_element}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['group_id, semester_id, week_number, week_day, number, subject_id', 'required'],
            ['group_id, semester_id, week_number, week_day, number, classroom_id, teacher_id, subject_id', 'numerical', 'integerOnly' => true],
            ['week_number', 'in', 'allowEmpty' => false, 'range' => [1, 2]],
            ['week_day', 'in', 'allowEmpty' => false, 'range' => [1, 2, 3, 4, 5, 6, 7]],
            ['id, group_id, semester_id, week_number, week_day, number, classroom_id, teacher_id, subject_id', 'safe', 'on' => 'search'],
        ];
    }

    /**
     * @return array
     */
    public function relations()
    {
        return [
            'subject' => [self::BELONGS_TO, 'Subjects', 'subject_id'],
            'classroom' => [self::BELONGS_TO, 'Classrooms', 'classroom_id'],
            'group' => [self::BELONGS_TO, 'Group', 'group_id'],
            'semester' => [self::BELONGS_TO, 'Semesters', 'semester_id'],
            'teacher' => [self::BELONGS_TO, 'Teachers', 'teacher_id'],
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
            'semester_id' => 'Семестр',
            'week_number' => 'Номер недели',
            'week_day' => 'Номер дня недели',
            'number' => 'Номер пары',
            'classroom_id' => 'Кабинет',
            'teacher_id' => 'Преподователь',
            'subject_id' => 'Предмет',
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
        $criteria->compare('semester_id', $this->semester_id);
        $criteria->compare('week_number', $this->week_number);
        $criteria->compare('week_day', $this->week_day);
        $criteria->compare('number', $this->number);
        $criteria->compare('classroom_id', $this->classroom_id);
        $criteria->compare('teacher_id', $this->teacher_id);
        $criteria->compare('subject_id', $this->subject_id);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return ScheduleElement
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
