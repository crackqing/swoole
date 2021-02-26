<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;
use App\Models\Task;
use EasySwoole\Mysqli\QueryBuilder;

class TaskUser extends AbstractModel
{
    protected $tableName = 'task_user';

    const TASK_USER_VISIBLE = ['task_id','status','add_time','username'];
    const TASK_USER_HIDEN = [];

    const TASK_STATUS_NORMAL = 1; //等于进行中才可以提交任务
    const TASK_STATUS_UNDER_REVIEW = 2; //审核中
    const TASK_STATUS_SUCCESS = 3;//审核成功
    const TASK_STATUS_GIVE_UP = 4; // 放弃任务状态4
    const TASK_STATUS_MALICIOUS =5;
    const TASK_FLAG_DEFAULT = 0;
    const TASK_FLAG_SUCCESS = 1;
    
    const TASK_STATUS_SWITCH_ARR = [
        self::TASK_STATUS_NORMAL    => '领取任务',
        self::TASK_STATUS_UNDER_REVIEW => '提交审核中',
        self::TASK_STATUS_SUCCESS => '审核成功',
        self::TASK_STATUS_GIVE_UP => '放弃任务',
        self::TASK_STATUS_MALICIOUS => '标记恶意提交',

    ];

    protected function task()
    {
        return $this->hasOne(Task::class, null, 'task_id', 'id');
    }

    public function getTaskIdAttr($value, $data)
    {
        return $value;
    }
}
