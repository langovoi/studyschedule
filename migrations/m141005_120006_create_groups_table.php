<?php

class m141005_120006_create_groups_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{group}}', [
            'id' => 'pk',
            'number' => 'integer',
            'owner_id' => 'integer'
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('group_owner_id_fk', '{{group}}', 'owner_id', '{{users}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{group}}');
    }

}