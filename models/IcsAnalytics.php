<?php

/**
 * @property integer $id
 * @property string $ip
 * @property string $headers
 * @property string $params
 * @property string $time
 */
class IcsAnalytics extends CActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{ics_analytics}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['ip, headers, params, time, useragent', 'required'],
            ['id, ip, headers, params, time, useragent', 'safe', 'on' => 'search'],
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
            'ip' => 'Ip',
            'useragent' => 'User Agent',
            'headers' => 'Headers',
            'params' => 'Params',
            'time' => 'Time',
        ];
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('ip', $this->ip, true);
        $criteria->compare('headers', $this->headers, true);
        $criteria->compare('params', $this->params, true);
        $criteria->compare('useragent', $this->params, true);
        $criteria->compare('time', $this->time, true);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return IcsAnalytics
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
