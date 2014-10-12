<?php

/**
 * @property integer $id
 * @property string $description
 * @property string $action
 * @property string $model
 * @property string $idModel
 * @property string $field
 * @property string $creationdate
 * @property string $userid
 */
class ActiveRecordLog extends CActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{log}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['creationdate', 'required'],
            ['description', 'length', 'max' => 255],
            ['action', 'length', 'max' => 20],
            ['model, field, userid', 'length', 'max' => 45],
            ['idModel', 'length', 'max' => 10],
            ['id, description, action, model, idModel, field, creationdate, userid', 'safe', 'on' => 'search'],
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
            'description' => 'Описание',
            'action' => 'Действие',
            'model' => 'Модель',
            'idModel' => 'ID Модели',
            'field' => 'Поле',
            'creationdate' => 'Дата',
            'userid' => 'ID Пользователя',
        ];
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('action', $this->action, true);
        $criteria->compare('model', $this->model, true);
        $criteria->compare('idModel', $this->idModel, true);
        $criteria->compare('field', $this->field, true);
        $criteria->compare('creationdate', $this->creationdate, true);
        $criteria->compare('userid', $this->userid, true);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return ActiveRecordLog
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
