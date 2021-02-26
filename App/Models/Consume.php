<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

//要以task_india 模型保持一致 操作类型相同方便查找与处理
class Consume extends AbstractModel
{
    const CONSUME_CURRENCY_GOLD = 'gold';   //金币
    const CONSUME_CURRENCY_CASH = 'cash';   //现金

    const CONSUME_TYPE_DEFAULT = 1;

    const CONSUME_TYPE_LOTTERY = 11; //抽奖得金币  gold
    const CONSUME_TYPE_LOTTERY_REFRESH = 12; //抽奖刷新机会
    const CONSUME_TYPE_LOTTERY_GIVE = 13; //抽奖一定次数赠送记录


    const CONSUME_TYPE_REGISTER_GIVE = 20; //注册送现金
    const CONSUME_TYPE_REGISTER_GIVE_SUPERIOR = 21; //注册送现金_返上级
    const CONSUME_TYPE_INVITES_CASH = 22; // 累积邀请人数得现金


    const CONSUME_TYPE_WITHDRAW = 23; //提现类型
    const CONSUME_TYPE_WITHDRAW_REFUND = 25; //退款操作
    const CONSUME_TYPE_TASK_REBATE = 24; //任务返利计算
    
    const CONSUME_TYPE_INVITE_GIVE = 30; //二维码分享绑定达到一定人数赠送处理
    const CONSUME_TYPE_DAILY_BONUS = 31; //每日签到赠送的记录




    const CONSUME_TYPE_BACKEND_COIN_ADD = 100; //后台玩家金额增加    金币
    const CONSUME_TYPE_BACKEND_COIN_REDUCE = 101; //后台玩家金额减少 金币

    const CONSUME_TYPE_BACKEND_MONEY_ADD = 102; //后台玩家金额增加    现金
    const CONSUME_TYPE_BACKEND_MONEY_REDUCE = 103; //后台玩家金额减少 现金


    const CONSUME_CURRENCY_ARR = [
        self::CONSUME_CURRENCY_GOLD => 'gold',
        self::CONSUME_CURRENCY_CASH => 'cash',
    ];

    //TYPE filter 中文
    const CONSUME_TYPE_ZN = [
        self::CONSUME_TYPE_LOTTERY  => '抽奖->大转盘',
        self::CONSUME_TYPE_LOTTERY_REFRESH => '抽奖->重置次数大转盘',
        self::CONSUME_TYPE_LOTTERY_GIVE => '抽奖->达到次数赠送',
        self::CONSUME_TYPE_REGISTER_GIVE => '填邀请码注册->送金币',

        self::CONSUME_TYPE_REGISTER_GIVE_SUPERIOR => '填邀请码注册->返上级',
        self::CONSUME_TYPE_INVITES_CASH => '累积邀请人数得现金',
        self::CONSUME_TYPE_WITHDRAW => '提现',
        self::CONSUME_TYPE_WITHDRAW_REFUND => '提现退款',

        self::CONSUME_TYPE_TASK_REBATE => '返利',
        
        self::CONSUME_TYPE_INVITE_GIVE => '绑定达到人数赠送',
        self::CONSUME_TYPE_DAILY_BONUS => '每日签到赠送',
        
        self::CONSUME_TYPE_BACKEND_COIN_ADD  => 'BACKEND->金币增加',
        self::CONSUME_TYPE_BACKEND_COIN_REDUCE => 'BACKEND->金币减少',

        self::CONSUME_TYPE_BACKEND_MONEY_ADD  => 'BACKEND->现金增加',
        self::CONSUME_TYPE_BACKEND_MONEY_REDUCE => 'BACKEND->现金减少',
    ];

    

    //TYPE filter English
    const CONSUME_TYPE_EN =[
        self::CONSUME_TYPE_LOTTERY  => 'lottery',
        self::CONSUME_TYPE_LOTTERY_REFRESH => 'lottery_refresh',
        self::CONSUME_TYPE_LOTTERY_GIVE => 'lottery_give',
        self::CONSUME_TYPE_REGISTER_GIVE => 'register_give',

        self::CONSUME_TYPE_REGISTER_GIVE_SUPERIOR => 'superior_give',
        self::CONSUME_TYPE_INVITES_CASH => 'invites_cash',
        self::CONSUME_TYPE_WITHDRAW => 'withdraw',
        self::CONSUME_TYPE_WITHDRAW_REFUND => 'withdraw_refund',

        self::CONSUME_TYPE_TASK_REBATE => 'rebate',
        
        self::CONSUME_TYPE_INVITE_GIVE => 'invite_give',
        self::CONSUME_TYPE_DAILY_BONUS => 'daily_bonus',

        self::CONSUME_TYPE_BACKEND_COIN_ADD  => 'BACKEND->coin_add',
        self::CONSUME_TYPE_BACKEND_COIN_REDUCE => 'BACKEND->coin_reduce',
        
        self::CONSUME_TYPE_BACKEND_MONEY_ADD  => 'BACKEND->money_add',
        self::CONSUME_TYPE_BACKEND_MONEY_REDUCE => 'BACKEND->money_reduce',
    ];
    

    protected $tableName = 'consume';
 

    //返回模型 统一设置 用于全接口处理
    public function getTypeAttr($value, $data)
    {
        return self::CONSUME_TYPE_EN[$value] ?? $value;
    }

    public function getConsumeAttr($value, $data)
    {
        return $value >= 0 ? (int)$value :$value;
    }
}
