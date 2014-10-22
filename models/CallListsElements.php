<?php

/**
 * @property integer $id
 * @property integer $number
 * @property string $start_time
 * @property string $end_time
 * @property integer $call_list_id
 *
 * @property CallLists $call_list
 */
class CallListsElements extends CActiveRecord
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
        return '{{call_lists_elements}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['number, call_list_id', 'required'],
            ['start_time, end_time', 'required'],
            ['number, call_list_id', 'numerical', 'integerOnly' => true],
            ['start_time, end_time', 'match', 'pattern' => '/(2[0-3]|[01][0-9]):[0-5][0-9]/', 'message' => 'Поле должно быть в формате ЧЧ:ММ'],
            ['start_time, end_time', 'timeCheck'],
            ['number', 'numberCheck'],
            ['id, number, start_time, end_time, call_list_id', 'safe', 'on' => 'search'],
        ];
    }

    /**
     * @return array
     */
    public function relations()
    {
        return [
            'call_list' => [self::BELONGS_TO, 'CallLists', 'call_list_id'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'number' => 'Номер пары',
            'start_time' => 'Время начала',
            'end_time' => 'Время конца',
            'call_list_id' => 'Список звонков',
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
        $criteria->compare('start_time', $this->start_time, true);
        $criteria->compare('end_time', $this->end_time, true);
        $criteria->compare('call_list_id', $this->call_list_id);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return CallListsElements
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    protected function afterFind()
    {
        $this->start_time = substr($this->start_time, 0, 5);
        $this->end_time = substr($this->end_time, 0, 5);

        return parent::afterFind();
    }

    public function numberCheck($attribute)
    {
        if (($model = CallListsElements::model()->findByAttributes([$attribute => $this->$attribute, 'call_list_id' => $this->call_list_id])) && $model->id !== $this->id)
            $this->addError($attribute, 'В данном списке уже есть элемент с этим номером пары');
    }

    public function timeCheck($attribute)
    {
        if (($model = CallListsElements::model()->find('start_time <= :time AND end_time >= :time AND call_list_id = :call_list_id', [':time' => $this->$attribute, ':call_list_id' => $this->call_list_id])) && $model->id !== $this->id)
            $this->addError($attribute, 'Время пары не может пересекаться с другой');
    }
}
