<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

class Task extends AbstractModel
{
    const TASK_STATUS_DEFAULT = 0;
    const TASK_STATUS_CLOSE = 1;
    //后续的 1：N  join不用自带的orm来写   1 为relation_ 2为 relation_2
    const TASK_FIELD = ['task.id','title','task.info','content','t_id','total_price'
    ,'total_number','receive_number','link_info','end_time','task.created_at','task_group.name as relation_name',
    'task_group.info as relation_info','task_group.icon as relation_icon','task_group.sort as relation_sort'];
    protected $tableName = 'task';

    public function taskGroup()
    {
        return $this->hasOne(TaskGroup::class, null, 't_id', 'id');
    }

    public function lang()
    {
        return $this->hasOne(Lang::class, null, 'lang_id', 'id');
    }

    public function getEndTimeAttr($value, $data)
    {
        return !empty($value) ? strtotime($value) : 0;
    }
}
