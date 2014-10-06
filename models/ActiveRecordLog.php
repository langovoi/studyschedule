<?php

/**
 * This is the model class for table "log".
 *
 * The followings are the available columns in table 'log':
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
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return [
			['creationdate', 'required'],
			['description', 'length', 'max' => 255],
			['action', 'length', 'max' => 20],
			['model, field, userid', 'length', 'max' => 45],
			['idModel', 'length', 'max' => 10],
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			['id, description, action, model, idModel, field, creationdate, userid', 'safe', 'on' => 'search'],
		];
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'description' => 'Description',
			'action' => 'Action',
			'model' => 'Model',
			'idModel' => 'Id Model',
			'field' => 'Field',
			'creationdate' => 'Creationdate',
			'userid' => 'Userid',
		];
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

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
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ActiveRecordLog the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}
}
