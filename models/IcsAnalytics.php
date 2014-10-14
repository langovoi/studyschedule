<?php

/**
 * @property integer $id
 * @property string $ip
 * @property string $useragent
 * @property string $group
 * @property string $time
 * @property string $unique_id
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
            ['ip, ip, group, useragent, time, unique_id', 'required'],
            ['ip, ip, group, useragent, time, unique_id', 'safe', 'on' => 'search'],
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
            'group' => 'Group',
            'time' => 'Time',
            'unique_id' => 'Unique ID',
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
        $criteria->compare('useragent', $this->useragent, true);
        $criteria->compare('group', $this->group, true);
        $criteria->compare('unique_id', $this->unique_id, true);
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
