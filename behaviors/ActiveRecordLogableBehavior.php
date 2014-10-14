<?php

class ActiveRecordLogableBehavior extends CActiveRecordBehavior
{
    private $_oldattributes = [];

    public function afterSave($event)
    {
        if (!Yii::app() instanceof CConsoleApplication) {
            if (!$this->Owner->isNewRecord) {

                // new attributes
                $newattributes = $this->Owner->getAttributes();
                $oldattributes = $this->getOldAttributes();

                // compare old and new
                foreach ($newattributes as $name => $value) {
                    if (!empty($oldattributes)) {
                        $old = $oldattributes[$name];
                    } else {
                        $old = '';
                    }

                    if ($value != $old) {
                        //$changes = $name . ' ('.$old.') => ('.$value.'), ';

                        $log = new ActiveRecordLog;
                        $log->description = 'User ' . Yii::app()->user->Name
                            . ' changed ' . $name . ' for '
                            . get_class($this->Owner)
                            . '[' . $this->Owner->getPrimaryKey() . '].';
                        $log->action = 'UPDATE';
                        $log->model = get_class($this->Owner);
                        $log->idModel = $this->Owner->getPrimaryKey();
                        $log->field = $name;
                        $log->creationdate = date("Y-m-d H:i:s");
                        $log->userid = Yii::app()->user->id;
                        $log->save();
                    }
                }
            } else {
                $log = new ActiveRecordLog;
                $log->description = 'User ' . Yii::app()->user->Name
                    . ' created ' . get_class($this->Owner)
                    . '[' . $this->Owner->getPrimaryKey() . '].';
                $log->action = 'CREATE';
                $log->model = get_class($this->Owner);
                $log->idModel = $this->Owner->getPrimaryKey();
                $log->field = '';
                $log->creationdate = date("Y-m-d H:i:s");
                $log->userid = Yii::app()->user->id;
                $log->save();
            }
        }
    }

    public function afterDelete($event)
    {
        if (!Yii::app() instanceof CConsoleApplication) {
            $log = new ActiveRecordLog;
            $log->description = 'User ' . Yii::app()->user->Name . ' deleted '
                . get_class($this->Owner)
                . '[' . $this->Owner->getPrimaryKey() . '].';
            $log->action = 'DELETE';
            $log->model = get_class($this->Owner);
            $log->idModel = $this->Owner->getPrimaryKey();
            $log->field = '';
            $log->creationdate = date("Y-m-d H:i:s");
            $log->userid = Yii::app()->user->id;
            $log->save();
        }
    }

    public function afterFind($event)
    {
        // Save old values
        $this->setOldAttributes($this->Owner->getAttributes());
    }

    public function getOldAttributes()
    {
        return $this->_oldattributes;
    }

    public function setOldAttributes($value)
    {
        $this->_oldattributes = $value;
    }
}