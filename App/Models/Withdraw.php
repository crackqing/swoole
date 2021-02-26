<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

//配置表 对应ID值固定死 不变动
class Withdraw extends AbstractModel
{
    const WITHDRAW_STATUS_DEFAULT = 0; //正常
    const WITHDRAW_STATUS_SUCCESS = 1; //成功
    const WITHDRAW_STATUS_FAILD = 2; //失败

    const WITHDRAW_NOTIFY_DEFAULT = 0; //第三方->回调状态 尚未回调
    const WITHDRAW_NOTIFY_SUCCESS = 1; //第三方->回调状态 验证参数成功修改
    const WITHDRAW_NOTIFY_PROCESS = 2; //线程处理

    const WITHDRAW_STATUS_ZH = [
        self::WITHDRAW_STATUS_DEFAULT => '提现审核',
        self::WITHDRAW_STATUS_SUCCESS => '提现成功',
        self::WITHDRAW_STATUS_FAILD => '提现失败',
    ];

    const WITHDRAW_NOTIFY_ZH = [
        self::WITHDRAW_NOTIFY_DEFAULT => '尚未回调',
        self::WITHDRAW_NOTIFY_SUCCESS => '回调成功',
    ];

    const WITHDRAW_STATUS_EN = [
        self::WITHDRAW_STATUS_DEFAULT => 'Withdrawal review',
        self::WITHDRAW_STATUS_SUCCESS => 'Withdraw successfully',
        self::WITHDRAW_STATUS_FAILD => 'Withdrawal failed, funds returned',
    ];

    protected $tableName = 'withdraw';

    public function getStatusAttr($value, $data)
    {
        return isset(self::WITHDRAW_STATUS_EN[$value]) ? self::WITHDRAW_STATUS_EN[$value] : $value;
    }
}
