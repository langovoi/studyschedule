<?php

class m140925_163944_create_rbac_tables extends CDbMigration
{
    public function safeUp()
    {
        $this->createTable('{{AuthItem}}', [
            'name' => 'varchar(64) not null',
            'type' => 'integer not null',
            'description' => 'text',
            'bizrule' => 'text',
            'data' => 'text',
        ], 'ENGINE=InnoDB');

        $this->addPrimaryKey('primary', '{{AuthItem}}', 'name');

        $this->createTable('{{AuthItemChild}}', [
            'parent' => 'varchar(64) not null',
            'child' => 'varchar(64) not null',
        ], 'ENGINE=InnoDB');

        $this->addPrimaryKey('primary', '{{AuthItemChild}}', 'parent,child');
        $this->addForeignKey('foreign_parent', '{{AuthItemChild}}', 'parent', '{{AuthItem}}', 'name', 'cascade', 'cascade');
        $this->addForeignKey('foreign_child', '{{AuthItemChild}}', 'child', '{{AuthItem}}', 'name', 'cascade', 'cascade');

        $this->createTable('{{AuthAssignment}}', [
            'itemname' => 'varchar(64) not null',
            'userid' => 'varchar(64) not null',
            'bizrule' => 'text',
            'data' => 'text',
        ], 'ENGINE=InnoDB');

        $this->addPrimaryKey('primary', '{{AuthAssignment}}', 'itemname,userid');
        $this->addForeignKey('foreign_itemname', '{{AuthAssignment}}', 'itemname', '{{AuthItem}}', 'name', 'cascade', 'cascade');

        $auth = Yii::app()->authManager;

        $role = $auth->createRole('guest', 'Гость');
        $role = $auth->createRole('admin', 'Администратор');
        $role = $auth->createRole('user', 'Пользователь');

        $auth->assign('admin', 1);
    }

    public function safeDown()
    {
        $this->dropTable('{{AuthAssignment}}');
        $this->dropTable('{{AuthItemChild}}');
        $this->dropTable('{{AuthItem}}');
    }
}