<?php

class m140927_063653_create_semesters_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{semesters}}', [
            'id' => 'pk',
            'name' => 'string NOT NULL',
            'start_date' => 'date NOT NULL',
            'end_date' => 'date NOT NULL',
            'week_number' => 'integer NOT NULL',
            'call_list' => 'integer NOT NULL',
            'call_list_short' => 'integer NOT NULL',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('semesters_call_list_fk', '{{semesters}}', 'call_list', '{{call_lists}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('semesters_call_list_short_fk', '{{semesters}}', 'call_list_short', '{{call_lists}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{semesters}}');
    }

}