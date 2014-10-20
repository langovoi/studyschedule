<?php

class m141020_091745_fix_log extends CDbMigration
{
    public function safeUp()
    {
		/** @var ActiveRecordLog $log */
		foreach (ActiveRecordLog::model()->findAllByAttributes([], 'description LIKE :text', [':text' => '%User Guest%']) as $log) {
			$log->userid = null;
			$log->save();
		}

	}

    public function safeDown()
    {
    }
}