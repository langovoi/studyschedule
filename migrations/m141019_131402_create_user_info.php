<?php

class m141019_131402_create_user_info extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn('{{users}}', 'firstname', 'string NOT NULL');
        $this->addColumn('{{users}}', 'lastname', 'string NOT NULL');
    }

    public function safeDown()
    {
        $this->dropColumn('{{users}}', 'firstname');
        $this->dropColumn('{{users}}', 'lastname');
    }
}