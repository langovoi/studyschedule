<?php

class m140926_105324_create_classrooms_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{classrooms}}', [
            'id' => 'pk',
            'name' => 'varchar(255) not null',
            'owner_id' => 'integer NULL DEFAULT NULL'
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('classrooms_owner_fk', '{{classrooms}}', 'owner_id', '{{users}}', 'id', 'set null', 'no action');
    }

    public function safeDown()
    {
        $this->dropTable('{{classrooms}}');
    }

}