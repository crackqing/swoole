<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

class TaskGroup extends AbstractModel
{
    const TASK_GROUP_STATE_DEFAULT = 0;
    const TASK_GROUP_STATE_CLOSE = 1;
    
    const TASK_GROUP_TABLE = 'task_group';
    
    protected $tableName = 'task_group';

    protected function getIconAttr($value, $data)
    {
        return DOMAIN_ADMIN.$value;
    }
}
