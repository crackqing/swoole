<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

//每日签到表 -> 定时任务处理
class DailyBonus extends AbstractModel
{
    protected $tableName = 'daily_bonus';
}
