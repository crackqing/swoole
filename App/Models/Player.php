<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

//支持两种 id pid & path 代理层级 ,也可单独维护关系表
class Player extends AbstractModel
{
    protected $tableName = 'player';
    const RELATIONS_NEXT_LEVEL = 1; //直属下级人数 parent_id  一共三级分销 不需要查找所有层级人数
    const RELATIONS_NEXT_N_LEVEL = 2; // path(,like) + level(+2) 查找下级所有人数
    const RELATIONS_NEXT_N_N_LEVEL = 3; // path(,like) + level(+3)  查找下下级所有人数

    const PLAYER_RELATIONS_P_ID = 'parent_id'; //一级 记录对应ID 方便直接查找  或者上级层级索引多种方法提供
    const PLAYER_RELATIONS_P_P_ID = 'p_pid'; //二级
    const PLAYER_RELATIONS_P_P_P_ID = 'p_p_pid'; //三级

    const REDIS_PLAYER_HMSET = 'USER_INFO:';
    const REDIS_PLAYER_HMSET_ID = 'id';
    const REDIS_PLAYER_HMSET_TOKEN = 'JWT-TOKEN';

    const PLAYER_CASH_FIELD_MONEY = 'money';
    const PLAYER_CASH_FIELD_COIN = 'coin';

    const PLAYER_RESULT_ARRAY = [''];

    const PLAYER_STATUS_DEFAULT = 0; //正常
    const PLAYER_STATUS_LOCK = 1; // 帐号被封,情况下无法登录


    public function playerInfo()
    {
        return $this->hasOne(PlayerInfo::class, null, 'id', 'id');
    }

    public function getNicknameAttr($value, $data)
    {
        return !empty($value)? $value : 'default';
    }
}
