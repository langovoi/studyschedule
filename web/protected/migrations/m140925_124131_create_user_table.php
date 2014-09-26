<?php

class m140925_124131_create_user_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{users}}', [
            'id' => 'pk',
            'username' => 'varchar(120) NOT NULL',
            'email' => 'varchar(255) NOT NULL',
            'password' => 'varchar(32) NOT NULL',
        ], 'ENGINE=InnoDB');

        $this->createIndex('users_username_unique', '{{users}}', 'username', true);
        $this->createIndex('users_email_unique', '{{users}}', 'email', true);

        $this->insert('{{users}}', ['username' => 'admin', 'email' => 'admin@localhost', 'password' => md5('admin')]);
    }

    public function safeDown()
    {
        $this->dropTable('{{users}}');
    }
}