<?php

class m141019_170646_create_invite_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{invite}}', [
            'id' => 'pk',
            'group_number' => 'integer NOT NULL',
            'name' => 'string NOT NULL',
            'email' => 'string NOT NULL',
            'text' => 'text NOT NULL',
            'status' => 'integer DEFAULT 0 NOT NULL',
            'time' => 'TIMESTAMP NOT NULL',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    public function safeDown()
    {
        $this->dropTable('{{invite}}');
    }
}