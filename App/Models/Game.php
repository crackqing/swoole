<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

class Game extends AbstractModel
{
    const GMAE_TURNTABLE = 1; //大转盘游戏ID为1 固定死
    const GAME_TABLE_NAME = 'game'; //db builder使用

    protected $tableName = 'game';

    /**
     * 默认Desc 做为比率或者概率值,不返回具体信息 function
     *  1.当前框架版本get不能指定使用模型,差点意思...Laravel ORM
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    protected function getContentAttr($value, $data)
    {
        // if (!empty($value)) {
        //     $value = json_decode($value, true);
        //     foreach ($value as $k => $v) {
        //         unset($value[$k]['desc']);
        //     }
        //     return $value;
        // }
        // return $value;
        return !empty($value) ? json_decode($value, true) : $value;
    }
    protected function getRestrictAttr($value, $data)
    {
        return !empty($value) ? json_decode($value, true) : $value;
    }
}
