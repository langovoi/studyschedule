<?php

/**
 * @property integer $id
 * @property string $date
 * @property integer $group_id
 * @property integer $number
 * @property integer $cancel
 * @property integer $classroom_id
 * @property integer $teacher_id
 * @property integer $subject_id
 * @property string $comment
 * @property string $owner
 *
 * @property Subjects $subject
 * @property Classrooms $classroom
 * @property Group $group
 * @property Teachers $teacher
 */
class GroupReplace extends CActiveRecord
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
        return '{{group_replace}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['date, group_id, number', 'required'],
            ['group_id, number, cancel, classroom_id, teacher_id, subject_id', 'numerical', 'integerOnly' => true],
            ['comment', 'safe'],
            ['cancel', 'checkCancel'],
            ['date', 'date', 'format' => 'yyyy-MM-dd'],
            ['number', 'numberCheck'],
            ['date', 'dateCheck'],
            ['owner', 'safe'],
            ['id, date, group_id, number, cancel, classroom_id, teacher_id, subject_id, comment, owner', 'safe', 'on' => 'search'],
        ];
    }

    public function checkCancel($attribute)
    {
        $value = $this->$attribute;
        if (!$value) {
            if (!$this->subject_id)
                $this->addError('subject_id', 'Выберите предмет');
        }
    }

    public function numberCheck($attribute)
    {
        if (GroupReplace::model()->findByAttributes(['group_id' => $this->group_id, 'date' => $this->date, $attribute => $this->$attribute])) {
            $this->addError($attribute, 'На данную дату и пару уже есть замена');
            $this->addError('date', 'На данную дату и пару уже есть замена');
        }
    }

    public function dateCheck($attribute)
    {
        /** @var Semesters $semester */
        $semester = Semesters::model()->byStartDate()->find();
        $time = strtotime($this->$attribute);
        if($time < strtotime($semester->start_date) || $time > strtotime($semester->end_date))
            $this->addError($attribute, 'Дата не может быть за пределами текущего семестра');
    }

    public function beforeSave()
    {
        if ($this->cancel) {
            $this->teacher_id = null;
            $this->classroom_id = null;
            $this->subject_id = null;
            $this->comment = null;
        }
        if (!Yii::app() instanceof CConsoleApplication)
            $this->owner = Yii::app()->user->name;
        return parent::beforeSave();
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
            'teacher' => [self::BELONGS_TO, 'Teachers', 'teacher_id'],
        ];
    }

    public function byDate()
    {
        $this->dbCriteria->order = 'date DESC';
        return $this;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Дата',
            'group_id' => 'Группа',
            'number' => 'Номер пары',
            'cancel' => 'Отменить?',
            'classroom_id' => 'Кабинет',
            'teacher_id' => 'Преподователь',
            'subject_id' => 'Предмет',
            'comment' => 'Комментарий',
            'owner' => 'Создал(а)',
        ];
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('date', $this->date, true);
        $criteria->compare('group_id', $this->group_id);
        $criteria->compare('number', $this->number);
        $criteria->compare('cancel', $this->cancel);
        $criteria->compare('classroom_id', $this->classroom_id);
        $criteria->compare('teacher_id', $this->teacher_id);
        $criteria->compare('subject_id', $this->subject_id);
        $criteria->compare('owner', $this->owner);
        $criteria->compare('comment', $this->comment, true);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return GroupReplace
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
