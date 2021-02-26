<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

//配置表 对应ID值固定死 不变动
class Config extends AbstractModel
{
    const CONFIG_REGISTER_GIVE_GOLD = 1; // 注册送金币
    const CONFIG_MONEY_INVITE_TOTAL = 2; // 累积邀请人数得现金
    const CONFIG_EXTENSION_SETTINGS = 3;// 系统推广配置 比率设置
    const CONFIG_DAILY_BONUS = 4;// 签到任务
    const CONFIG_WITHDRAW_LIMIT = 5; //提现限制

    const CONFIG_INDEX_COIN = 6; //首页金币显示


    protected $tableName = 'config';
}
