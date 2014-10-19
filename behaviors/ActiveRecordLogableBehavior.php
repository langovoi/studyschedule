<?php

class ActiveRecordLogableBehavior extends CActiveRecordBehavior
{
    private $_oldattributes = [];

    public function afterSave($event)
    {
        if (!Yii::app() instanceof CConsoleApplication) {
            if (!$this->owner->isNewRecord) {

                // new attributes
                $newattributes = $this->owner->getAttributes();
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
                        $log->description = 'User ' . Yii::app()->user->name
                            . ' changed ' . $name . ' for '
                            . get_class($this->owner)
                            . '[' . $this->owner->getPrimaryKey() . '].';
                        $log->action = 'UPDATE';
                        $log->model = get_class($this->owner);
                        $log->idModel = $this->owner->getPrimaryKey();
                        $log->field = $name;
                        $log->creationdate = date("Y-m-d H:i:s");
                        $log->userid = Yii::app()->user->id;
                        $log->save();
                    }
                }
            } else {
                $log = new ActiveRecordLog;
                $log->description = 'User ' . Yii::app()->user->name
                    . ' created ' . get_class($this->owner)
                    . '[' . $this->owner->getPrimaryKey() . '].';
                $log->action = 'CREATE';
                $log->model = get_class($this->owner);
                $log->idModel = $this->owner->getPrimaryKey();
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
            $log->description = 'User ' . Yii::app()->user->name . ' deleted '
                . get_class($this->owner)
                . '[' . $this->owner->getPrimaryKey() . '].';
            $log->action = 'DELETE';
            $log->model = get_class($this->owner);
            $log->idModel = $this->owner->getPrimaryKey();
            $log->field = '';
            $log->creationdate = date("Y-m-d H:i:s");
            $log->userid = Yii::app()->user->id;
            $log->save();
        }
    }

    public function afterFind($event)
    {
        // Save old values
        $this->setOldAttributes($this->owner->getAttributes());
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