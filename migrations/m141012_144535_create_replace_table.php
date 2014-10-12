<?php

class m141012_144535_create_replace_table extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{group_replace}}', [
            'id' => 'pk',
            'date' => 'date NOT NULL',
            'group_id' => 'integer NOT NULL',
            'number' => 'integer NOT NULL',
            'cancel' => 'integer DEFAULT 0',
            'classroom_id' => 'integer NULL DEFAULT NULL',
            'teacher_id' => 'integer NULL DEFAULT NULL',
            'subject_id' => 'integer NULL DEFAULT NULL',
			'comment' => 'text NULL DEFAULT NULL',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('group_replace_group_id_fk', '{{group_replace}}', 'group_id', '{{group}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('group_replace_classroom_id_fk', '{{group_replace}}', 'classroom_id', '{{classrooms}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('group_replace_teacher_id_fk', '{{group_replace}}', 'teacher_id', '{{teachers}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('group_replace_subject_id_fk', '{{group_replace}}', 'subject_id', '{{subjects}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createIndex('group_replace_unique', '{{group_replace}}', 'date,group_id,number', true);
    }

    public function safeDown()
    {
        $this->dropTable('{{group_replace}}');
    }
}