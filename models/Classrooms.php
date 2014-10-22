<?php

/**
 *
 * @property integer $id
 * @property string $name
 * @property integer $owner_id
 *
 * @property Users $owner
 * @property ScheduleElement[] $schedule_elements
 */
class Classrooms extends CActiveRecord
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
        return '{{classrooms}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'unique', 'caseSensitive' => false],
            ['owner_id', 'numerical', 'integerOnly' => true],
            ['name', 'length', 'max' => 255],
            ['id, name, owner_id', 'safe', 'on' => 'search'],
        ];
    }

    public function byName()
    {
        $this->dbCriteria->order = 'name ASC';
        return $this;
    }

    /**
     * @return array
     */
    public function relations()
    {
        return [
            'owner' => [self::BELONGS_TO, 'Users', 'owner_id'],
            'schedule_elements' => [self::HAS_MANY, 'ScheduleElemenet', 'classroom_id'],
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
            'owner_id' => 'Создал',
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
        $criteria->compare('owner_id', $this->owner_id);

        return new CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }

    /**
     * @param string $className
     * @return Classrooms
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeSave()
    {
        if (!Yii::app()->user->checkAccess('admin') && !Yii::app() instanceof CConsoleApplication && $this->scenario == 'insert')
            $this->owner_id = Yii::app()->user->getId();
        return parent::beforeSave();
    }
}
