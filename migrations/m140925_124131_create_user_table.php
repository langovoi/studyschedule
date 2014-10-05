<?php

class m140925_124131_create_user_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{users}}', [
            'id' => 'pk',
            'username' => 'string NOT NULL',
            'email' => 'string NOT NULL',
            'password' => 'varchar(32) NOT NULL',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('users_username_unique', '{{users}}', 'username', true);
        $this->createIndex('users_email_unique', '{{users}}', 'email', true);

        $this->insert('{{users}}', ['username' => 'admin', 'email' => 'admin@localhost', 'password' => md5('admin')]);
    }

    public function safeDown()
    {
        $this->dropTable('{{users}}');
    }
}