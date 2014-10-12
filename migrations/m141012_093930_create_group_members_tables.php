<?php

class m141012_093930_create_group_members_tables extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{group_invite}}', [
            'id' => 'pk',
            'group_id' => 'integer NOT NULL',
            'email' => 'string NOT NULL',
            'status' => 'integer DEFAULT 0',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('group_invite_group_id_fk', '{{group_invite}}', 'group_id', '{{group}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{group_member}}', [
            'id' => 'pk',
            'group_id' => 'integer NOT NULL',
            'user_id' => 'integer NOT NULL',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('group_member_group_id_fk', '{{group_member}}', 'group_id', '{{group}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('group_member_user_id_fk', '{{group_member}}', 'user_id', '{{users}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createIndex('group_member_unique', '{{group_member}}', 'group_id,user_id', true);

    }

    public function safeDown()
    {
        $this->dropTable('{{group_invite}}');

        $this->dropTable('{{group_member}}');
    }

}