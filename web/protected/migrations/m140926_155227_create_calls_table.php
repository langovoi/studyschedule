<?php

class m140926_155227_create_calls_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{call_lists}}', [
            'id' => 'pk',
            'name' => 'varchar(255) not null'
        ], 'ENGINE=InnoDB');

        $this->createIndex('call_lists_name_unique', '{{call_lists}}', 'name', true);

        $this->createTable('{{call_lists_elements}}', [
            'id' => 'pk',
            'number' => 'int(11) not null',
            'start_time' => 'time',
            'end_time' => 'time',
            'call_list_id' => 'int(11) not null'
        ], 'ENGINE=InnoDB');

        $this->addForeignKey('call_lists_elements_call_id_fk', '{{call_lists_elements}}', 'call_list_id', '{{call_lists}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('call_lists_elements_number_unique', '{{call_lists_elements}}', 'number,call_list_id', true);
    }

    public function safeDown()
    {

        $this->dropTable('{{call_lists_elements}}');
        $this->dropTable('{{call_lists}}');
    }

}