<?php

class m141012_135431_add_col_for_invite extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn('{{group_invite}}', 'hash', 'varchar(32) DEFAULT NULL');
    }

    public function safeDown()
    {
        $this->dropColumn('{{group_invite}}', 'hash');
    }
}