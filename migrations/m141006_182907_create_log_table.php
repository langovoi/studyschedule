<?php

class m141006_182907_create_log_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{log}}', [
            'id' => 'pk',
            'description' => 'VARCHAR(255) NULL',
            'action' => 'VARCHAR(20) NULL',
            'model' => 'VARCHAR(45) NULL',
            'idModel' => 'INTEGER UNSIGNED NULL',
            'field' => 'VARCHAR(45) NULL',
            'creationdate' => 'TIMESTAMP NOT NULL',
            'userid' => 'VARCHAR(45) NULL',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    public function safeDown()
    {
        $this->dropTable('{{log}}');
    }
}