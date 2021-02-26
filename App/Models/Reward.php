<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

//手动领取->奖励表操作 处理状态
class Reward extends AbstractModel
{
    const REWARD_TYPE_DAILY_BONUS = 5;  //签到任务
    const REWARD_TYPE_INVITE_CASH = 6; //邀请送 现金活动

    protected $tableName = 'reward';
}
