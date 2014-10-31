<?php

class m141101_095524_vk_auto_post_status extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn('{{group_autopost}}', 'status', 'integer DEFAULT ' . GroupAutopost::STATUS_ACTIVE);
    }

    public function safeDown()
    {
        $this->dropColumn('{{group_autopost}}', 'status');
    }
}