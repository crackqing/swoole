<?php

namespace App\Lib;

//抽奖 -> 适合九宫格 大转盘
trait Lottery
{
    /**
     * 随机概率 function
     *
     * @param array $arr
     * @return integer
     */
    public function getRand(array $arr = []) : int
    {
        $rid = -1;
        if (!is_array($arr) & empty($arr)) {
            return $rid;
        }
        $arrSum = array_sum($arr);
        foreach ($arr as $k => $v) {
            $randNum = mt_rand(1, $arrSum);
            if ($randNum <= $v) {
                $rid = $k;
                break;
            } else {
                $arrSum -= $v;
            }
        }
        unset($arr);
        return $rid;
    }
}
