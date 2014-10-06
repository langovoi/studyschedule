<?php

class m141006_175849_fix_fk extends CDbMigration
{
    public function safeUp()
    {
		$this->dropForeignKey('classrooms_owner_fk', '{{classrooms}}');
		$this->addForeignKey('classrooms_owner_fk', '{{classrooms}}', 'owner_id', '{{users}}', 'id', 'RESTRICT', 'CASCADE');

		$this->dropForeignKey('teachers_owner_fk', '{{teachers}}');
		$this->addForeignKey('teachers_owner_fk', '{{teachers}}', 'owner_id', '{{users}}', 'id', 'RESTRICT', 'CASCADE');

		$this->dropForeignKey('subjects_owner_fk', '{{subjects}}');
		$this->addForeignKey('subjects_owner_fk', '{{subjects}}', 'owner_id', '{{users}}', 'id', 'RESTRICT', 'CASCADE');

		$this->dropForeignKey('group_owner_id_fk', '{{group}}');
        $this->addForeignKey('group_owner_id_fk', '{{group}}', 'owner_id', '{{users}}', 'id', 'RESTRICT', 'CASCADE');

		$this->dropForeignKey('schedule_element_subject_id_fk', '{{schedule_element}}');
        $this->addForeignKey('schedule_element_subject_id_fk', '{{schedule_element}}', 'subject_id', '{{subjects}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
    }

}