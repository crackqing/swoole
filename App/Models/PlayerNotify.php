<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

//通知表,未读消息等操作
class PlayerNotify extends AbstractModel
{
    const ANNOUNCE_ID_REGISTER_PUSH = 10001; //后端 回调推送通知

    protected $tableName = 'player_notify';
}
