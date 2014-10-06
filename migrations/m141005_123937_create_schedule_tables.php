<?php

class m141005_123937_create_schedule_tables extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{schedule_element}}', [
            'id' => 'pk',
            'group_id' => 'integer NOT NULL',
            'semester_id' => 'integer NOT NULL',
            'week_number' => 'integer NOT NULL',
            'week_day' => 'integer NOT NULL',
            'number' => 'integer NOT NULL',
            'classroom_id' => 'integer NULL DEFAULT NULL',
            'teacher_id' => 'integer NULL DEFAULT NULL',
            'subject_id' => 'integer NOT NULL',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('schedule_element_group_id_fk', '{{schedule_element}}', 'group_id', '{{group}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('schedule_element_semester_id_fk', '{{schedule_element}}', 'semester_id', '{{semesters}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('schedule_element_classroom_id_fk', '{{schedule_element}}', 'classroom_id', '{{classrooms}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('schedule_element_teacher_id_fk', '{{schedule_element}}', 'teacher_id', '{{teachers}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('schedule_element_subject_id_fk', '{{schedule_element}}', 'subject_id', '{{subjects}}', 'id', 'CASCADE', 'CASCADE');

		$this->createIndex('schedule_unique_index', '{{schedule_element}}', 'group_id,semester_id,week_number,week_day,number', true);
    }

    public function safeDown()
    {
		$this->dropTable('{{schedule_element}}');
    }

}