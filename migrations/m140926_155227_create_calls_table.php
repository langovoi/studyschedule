<?php

class m140926_155227_create_calls_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{call_lists}}', [
            'id' => 'pk',
            'name' => 'string NOT NULL'
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('call_lists_name_unique', '{{call_lists}}', 'name', true);

        $this->createTable('{{call_lists_elements}}', [
            'id' => 'pk',
            'number' => 'integer NOT NULL',
            'start_time' => 'time NOT NULL',
            'end_time' => 'time NOT NULL',
            'call_list_id' => 'integer NOT NULL'
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('call_lists_elements_call_id_fk', '{{call_lists_elements}}', 'call_list_id', '{{call_lists}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('call_lists_elements_number_unique', '{{call_lists_elements}}', 'number,call_list_id', true);
    }

    public function safeDown()
    {

        $this->dropTable('{{call_lists_elements}}');
        $this->dropTable('{{call_lists}}');
    }

}