<?php

class m141019_181900_add_hash_to_invite extends CDbMigration
{
    public function safeUp()
    {
        $this->addColumn('{{invite}}', 'hash', 'varchar(32) DEFAULT NULL');

        foreach (Invite::model()->findAll() as $invite) {
            $invite->setAttribute('hash', md5($invite->email . $invite->group_number . uniqid()));
            $invite->save();
        }
    }

    public function safeDown()
    {
        $this->dropColumn('{{invite}}', 'hash');
    }
}