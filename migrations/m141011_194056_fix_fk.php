<?php

class m141011_194056_fix_fk extends CDbMigration
{
	public function safeUp()
	{
		$this->dropForeignKey('schedule_element_semester_id_fk', '{{schedule_element}}');
		$this->addForeignKey('schedule_element_semester_id_fk', '{{schedule_element}}', 'semester_id', '{{semesters}}', 'id', 'RESTRICT', 'CASCADE');

		$this->dropForeignKey('schedule_element_group_id_fk', '{{schedule_element}}');
		$this->addForeignKey('schedule_element_group_id_fk', '{{schedule_element}}', 'group_id', '{{group}}', 'id', 'RESTRICT', 'CASCADE');
	}

	public function safeDown()
	{
	}

}