<?php

class m141005_152401_create_short_days_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{short_day}}', [
            'id' => 'pk',
            'name' => 'text NOT NULL',
            'date' => 'date NOT NULL',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('short_day_unique', '{{short_day}}', 'date', true);
    }

    public function safeDown()
    {
        $this->dropTable('{{short_day}}');
    }
}