<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

class Enum extends AbstractModel
{
    const NORMAL = 1;
    const PLACEHOLDER = 0;
    const REDIS_PUSH_TASK = 'task:queue';
    
    protected $tableName = 'enum';
}
