<?php

class m141014_153006_ics_analytics_unique extends CDbMigration
{
	public function safeUp()
	{
		$this->addColumn('{{ics_analytics}}', 'unique_id', 'varchar(32) NOT NULL');
	}

	public function safeDown()
	{
		$this->dropColumn('{{ics_analytics}}', 'unique_id');
	}

}