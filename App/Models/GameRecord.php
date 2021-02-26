<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

class GameRecord extends AbstractModel
{
    protected $tableName = 'game_record';

    public function getPrizeAttr($value,$data)
    {
        return !empty($value)? json_decode($value,true) : $value;
    }
}
