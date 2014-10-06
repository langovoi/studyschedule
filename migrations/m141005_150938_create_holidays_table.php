<?php

class m141005_150938_create_holidays_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{holiday}}', [
            'id' => 'pk',
            'name' => 'text NOT NULL',
            'date' => 'date NOT NULL',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('holiday_unique', '{{holiday}}', 'date', true);
    }

    public function safeDown()
    {
        $this->dropTable('{{holiday}}');
    }

}