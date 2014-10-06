<?php

class m140926_112935_create_teachers_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{teachers}}', [
            'id' => 'pk',
            'lastname' => 'string NOT NULL',
            'firstname' => 'string NOT NULL',
            'middlename' => 'string NOT NULL',
            'owner_id' => 'integer NULL DEFAULT NULL'
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('teachers_owner_fk', '{{teachers}}', 'owner_id', '{{users}}', 'id', 'set null', 'no action');
    }

    public function safeDown()
    {
        $this->dropTable('{{teachers}}');
    }
}