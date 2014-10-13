<?php

class m141013_171131_create_ics_analytics extends CDbMigration
{
    public function safeUp()
    {
		$this->createTable('{{ics_analytics}}', [
			'id' => 'pk',
			'ip' => 'text NOT NULL',
			'useragent' => 'text NOT NULL',
			'headers' => 'text NOT NULL',
			'params' => 'text NOT NULL',
			'time' => 'TIMESTAMP NOT NULL'
		], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    public function safeDown()
    {
		$this->dropTable('{{ics_analytics}}');
    }
}