<?php

class m141013_175100_rework_ics_analytics extends CDbMigration
{
    public function safeUp()
    {
        IcsAnalytics::model()->deleteAll();
        $this->dropColumn('{{ics_analytics}}', 'headers');
        $this->dropColumn('{{ics_analytics}}', 'params');
        $this->addColumn('{{ics_analytics}}', 'group', 'text NOT NULL');
    }

    public function safeDown()
    {
        IcsAnalytics::model()->deleteAll();
        $this->addColumn('{{ics_analytics}}', 'headers', 'text NOT NULL');
        $this->addColumn('{{ics_analytics}}', 'params', 'text NOT NULL');
        $this->dropColumn('{{ics_analytics}}', 'group');
    }

}