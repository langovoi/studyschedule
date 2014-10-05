<?php

class m140927_063653_create_semesters_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{semesters}}', [
            'id' => 'pk',
            'name' => 'varchar(255) not null',
            'start_date' => 'date',
            'end_date' => 'date',
            'week_number' => 'integer',
            'call_list' => 'integer',
            'call_list_short' => 'integer',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('semesters_call_list_fk', '{{semesters}}', 'call_list', '{{call_lists}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('semesters_call_list_short_fk', '{{semesters}}', 'call_list_short', '{{call_lists}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{semesters}}');
    }

}