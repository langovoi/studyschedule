<?php

class m141020_084414_fix_ics_group_number extends CDbMigration
{
	public function safeUp()
	{
		$this->alterColumn('{{ics_analytics}}', 'group', 'integer NOT NULL');
	}

	public function safeDown()
	{
		$this->alterColumn('{{ics_analytics}}', 'group', 'text NOT NULL');
	}
}