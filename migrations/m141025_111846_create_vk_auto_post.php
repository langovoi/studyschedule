<?php

class m141025_111846_create_vk_auto_post extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{group_autopost}}', [
            'id' => 'pk',
            'access_token' => 'string NOT NULL',
            'page_id' => 'integer NOT NULL',
            'group_id' => 'integer NOT NULL',
            'hour' => 'integer NOT NULL'
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('group_autopost_group_id', '{{group_autopost}}', 'group_id', '{{group}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
		$this->dropTable('{{group_autopost}}');
    }
}