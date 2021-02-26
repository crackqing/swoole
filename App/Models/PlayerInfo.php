<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

class PlayerInfo extends AbstractModel
{
    protected $tableName = 'player_info';


    protected function getBankAttr($value,$data)
    {
        return !empty($value) ? json_decode($value,true) :  $value;
    }

    public function setBankAttr($value,$data)
    {
        return json_encode($value);
    }
}
