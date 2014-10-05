<?php

class m140926_114659_create_subjects_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{subjects}}', [
            'id' => 'pk',
            'name' => 'text NOT NULL',
            'owner_id' => 'integer DEFAULT NULL'
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('subjects_owner_fk', '{{subjects}}', 'owner_id', '{{users}}', 'id', 'set null', 'no action');
    }

    public function safeDown()
    {
        $this->dropTable('{{subjects}}');
    }
}