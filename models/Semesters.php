<?php

/**
 * @property integer $id
 * @property string $name
 * @property string $start_date
 * @property string $end_date
 * @property integer $week_number
 * @property integer $call_list
 * @property integer $call_list_short
 *
 * @method CallLists call_list_short
 * @method CallLists call_list
 * @method Semesters with
 */
class Semesters extends CActiveRecord
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
        return '{{semesters}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['name, start_date, end_date, week_number, call_list, call_list_short', 'required'],
            ['week_number, call_list, call_list_short', 'numerical', 'integerOnly' => true],
            ['name', 'length', 'max' => 255],
            ['start_date, end_date', 'date', 'format' => 'yyyy-MM-dd'],
            ['start_date,end_date', 'dateCheck'],
            ['start_date', 'dateIntervalCheck', 'end' => 'end_date'],
            ['id, name, start_date, end_date, week_number, call_list, call_list_short', 'safe', 'on' => 'search'],
        ];
    }

    public function dateCheck($attribute)
    {
        $time = strtotime($this->$attribute);
        if ($this->isNewRecord && $time < strtotime(date('Y-m-d'))) {
            $this->addError($attribute, 'Нельзя установить дату меньше сегоднешней');
        } elseif (($semester = Semesters::model()->find('start_date <= :date AND end_date >= :date', [':date' => $this->$attribute])) && $semester->id !== $this->id) {
            $this->addError($attribute, 'Уже есть семестр который перекрывают данную дату');
        }
    }

    public function dateIntervalCheck($attribute, $params)
    {
        $time_start = strtotime($this->$attribute);
        $time_end = strtotime($this->$params['end']);
        if ($time_start >= $time_end) {
            $this->addError($attribute, 'Дата начала должна быть меньше даты конца');
            $this->addError($params['end'], 'Дата конца должна быть больше даты начала');
        }
    }

    public function byStartDate()
    {
        $this->dbCriteria->order = 'start_date DESC';
        return $this;
    }

    /**
     * @return array
     */
    public function relations()
    {
        return [
            'call_list_short' => [self::BELONGS_TO, 'CallLists', 'call_list_short'],
            'call_list' => [self::BELONGS_TO, 'CallLists', 'call_list'],
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
            'start_date' => 'Дата начала',
            'end_date' => 'Дата конца',
            'week_number' => 'Номер первой недели',
            'call_list' => 'Список звонков',
            'call_list_short' => 'Список сокращенных звонков',
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
        $criteria->compare('start_date', $this->start_date, true);
        $criteria->compare('end_date', $this->end_date, true);
        $criteria->compare('week_number', $this->week_number);
        $criteria->compare('call_list', $this->call_list);
        $criteria->compare('call_list_short', $this->call_list_short);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return Semesters
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return Semesters|false
     */
    public function actual()
    {
        return $this->find('start_date <= :date AND end_date >= :date', [':date' => date('Y-m-d')]);
    }
}
