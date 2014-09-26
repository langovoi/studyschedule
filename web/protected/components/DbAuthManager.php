<?php

class DbAuthManager extends CDbAuthManager
{
    public $itemTable = '{{AuthItem}}';

    public $itemChildTable = '{{AuthItemChild}}';

    public $assignmentTable = '{{AuthAssignment}}';
}