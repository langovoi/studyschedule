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
            ['id, number, start_time, end_time, call_list_id', 'safe', 'on' => 'search'],
        ];
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $validator = CValidator::createValidator('unique', $this, 'call_list_id', [
                'criteria' => [
                    'condition' => '`number`=:number',
                    'params' => [
                        ':number' => $this->number
                    ]
                ]
            ]);
            $this->getValidatorList()->insertAt(0, $validator);

            return true;
        }
        return false;
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

    protected function afterFind()
    {
        $start_time = explode(':', $this->start_time);
        array_pop($start_time);
        $end_time = explode(':', $this->end_time);
        array_pop($end_time);
        $this->start_time = implode(':', $start_time);
        $this->end_time = implode(':', $end_time);
    }

    /**
     * @param string $className
     * @return CallListsElements
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
