<?php

class m141014_175016_rework_replace extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn('{{group_replace}}', 'owner', 'string NOT NULL');
        $replaces = GroupReplace::model()->findAll();
        foreach ($replaces as $replace) {
            $log = ActiveRecordLog::model()->findByAttributes(['model' => 'GroupReplace', 'idModel' => $replace->id, 'action' => 'CREATE']);
            if ($log) {
                $log = explode(' ', $log->description);
                $replace->owner = $log[1];
				$replace->save();
            }
        }
    }

    public function safeDown()
    {
        $this->dropColumn('{{group_replace}}', 'owner');
    }
}