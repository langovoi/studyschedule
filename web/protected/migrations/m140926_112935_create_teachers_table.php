<?php

class m140926_112935_create_teachers_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{teachers}}', [
            'id' => 'pk',
            'firstname' => 'varchar(255) not null',
            'lastname' => 'varchar(255) not null',
            'middlename' => 'varchar(255) not null',
            'owner_id' => 'integer NULL DEFAULT NULL'
        ]);

        $this->addForeignKey('teachers_owner_fk', '{{teachers}}', 'owner_id', '{{users}}', 'id', 'set null', 'no action');
    }

    public function safeDown()
    {
        $this->dropTable('{{teachers}}');
    }
}