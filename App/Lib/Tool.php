<?php
namespace App\Lib;

use App\Models\Player;
use App\Models\PlayerInfo;
use App\Models\Consume;
use App\Models\DailyTotal;
//CORE
use EasySwoole\Mysqli\QueryBuilder;
use App\Models\Config;

use GeoIp2\Database\Reader;

//工具类 或定义在func.php 都可以
trait Tool
{
    /**
     * 生成唯一标志
     * 标准的UUID格式为：xxxxxxxx-xxxx-xxxx-xxxxxx-xxxxxxxxxx(8-4-4-4-12) function
     * @return void
     */
    public function uuid()
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-'
                . substr($chars, 8, 4) . '-'
                . substr($chars, 12, 4) . '-'
                . substr($chars, 16, 4) . '-'
                . substr($chars, 20, 12);
        return $uuid ;
    }
    /**
     * App\Models\Player function
     *
     * @return void
     */
    public function getPlayer($playerId = '')
    {
        return  (new Player())->where([
            'id'    => !empty($playerId) ? $playerId :  $this->jwtData['id'],
        ])->get();
    }

    /**
     * App\Models\PlayerInfo function
     *
     * @return void
     */
    public function getPlayerInfo($playerId = '')
    {
        return  (new PlayerInfo())->where([
            'id'    => !empty($playerId) ? $playerId :  $this->jwtData['id'],
        ])->get();
    }

    /**
     * 玩家金币与现金统一更新操作    function
     *      1.加锁等处理.记录所有操作情况
     * @param [int] $playerId 玩家ID
     * @param [int] $money 现金
     * @param array $where where条件增加
     * @param string $type inc or dec
     * @param string $moneyType consume模型里面常量定义 gold & money
     * @return boolean
     */
    public function playerMoneyUpdateUntiy(
        int $playerId,
        int $money,
        array $where = [],
        string $type= 'inc',
        string $moneyType = 'coin'
    ) : bool {
        // $whereMerger = array_merge([$moneyType =>  QueryBuilder::$type((int)$money)], $where);
        $whereMerger = array_merge([$moneyType =>  QueryBuilder::$type($money)], $where);
        return Player::create()->update($whereMerger, ['id'=> $playerId]);
    }

    /**
     * 用户统一金币操作记录 + function
     *  1.1现金与金币 全记录 并增加每日统计 daily 处理好玩家的资金情况
     * @param integer $playerId 用户ID
     * @param integer $money 金币或者现金
     * @param integer $changeMoney 改变金额
     * @param string $currency 操作类型 gold cash
     * @param integer $type 具体类型参考模型上文件数字
     * @param array $content 具体类型的参考内容json
     * @return boolean
     */
    public function playerRecordUntiyAdd(
        int $playerId,
        int $money,
        int $changeMoney,
        string $currency = Consume::CONSUME_CURRENCY_GOLD,
        int $type = Consume::CONSUME_TYPE_DEFAULT,
        array $content = []
    ) :bool {
        $today = strtotime('today');
        $date = date('Y-m-d H:i:s', time());
        //daily
        $player = $this->getPlayer($playerId);
        if ($currency == Consume::CONSUME_CURRENCY_GOLD) {
            //gold -> coin
            $dailyTotal =  DailyTotal::create()->where([
                'player_id' => $playerId,
                'daily_time' => $today,
            ])->get();
            if (empty($dailyTotal)) {
                $data = [ //gold -> coin
                    'path'  => $player['path'],
                    'level' => $player['level'],
                    'player_id' => $playerId,
                    'coin'  => $changeMoney,
                    'daily_time' => strtotime('today'),
                    'created_at' => $date
                ];
                //如果类型为佣金
                if ($type == Consume::CONSUME_TYPE_TASK_REBATE) {
                    $data['commission'] = $changeMoney;
                    $data['activity'] = SYSTEM_NORMAL;
                }

                DailyTotal::create()->data($data)->save();
            } else {
                if ($type == Consume::CONSUME_TYPE_TASK_REBATE) { //佣金计算,相减就是去掉佣金的
                    $data['commission'] = $changeMoney;
                    DailyTotal::create()->update([
                        'coin'  => QueryBuilder::inc($changeMoney),
                        'commission'    =>  QueryBuilder::inc($changeMoney),
                        'activity'  => QueryBuilder::inc(),
                        'updated_at' => $date,
                    ], [
                        'player_id' => $playerId,
                        'daily_time' => $today,
                    ]);
                } else {
                    DailyTotal::create()->update([
                        'coin'  => QueryBuilder::inc($changeMoney),
                        'updated_at' => $date,
                    ], [
                        'player_id' => $playerId,
                        'daily_time' => $today,
                    ]);
                }
            }
            PlayerInfo::create()->update([
                'total_coin'    => QueryBuilder::inc($changeMoney)
            ], [
                'id'    => $playerId
            ]);
        }
        if ($currency == Consume::CONSUME_CURRENCY_CASH) {
            //cash -> money
            $dailyTotal =  DailyTotal::create()->where([
                'player_id' => $playerId,
                'daily_time' => $today,
            ])->get();
            if (empty($dailyTotal)) {
                $data = [ //gold -> coin
                    'path'  => $player['path'],
                    'level' => $player['level'],
                    'player_id' => $playerId,
                    'money'  => $changeMoney,
                    'daily_time' => strtotime('today'),
                    'created_at' => $date
                ];
                DailyTotal::create()->data($data)->save();
            } else {
                DailyTotal::create()->update([
                    'money'  => QueryBuilder::inc($changeMoney),
                    'updated_at' => $date,
                ], [
                    'player_id' => $playerId,
                    'daily_time' => $today,
                ]);
            }
            PlayerInfo::create()->update([
                'total_money'    => QueryBuilder::inc($changeMoney)
            ], [
                'id'    => $playerId
            ]);
        }
        //record
        $data = [
            'currency' => $currency,
            'player_id' => $playerId,
            'type'  => $type,
            'content'   => json_encode($content),
            'consume_time' => time(),
            'consume' => $changeMoney,
            'before_consume' => $money,
            'after_consume' => $money + $changeMoney,
            'created_at'    => $date,
        ];
        return Consume::create()->data($data)->save();
    }

    /**
     * 用户统一金币操作记录 - function
     *  只做记录,不做统计汇总处理
     * @param integer $playerId 用户ID
     * @param integer $money 金币或者现金
     * @param integer $changeMoney 改变金额
     * @param string $currency 操作类型 gold cash
     * @param integer $type 具体类型参考模型上文件数字
     * @param array $content 具体类型的参考内容json
     * @return boolean
     */
    public function playerRecordUntiyReduce(
        int $playerId,
        int $money,
        int $changeMoney,
        string $currency = Consume::CONSUME_CURRENCY_GOLD,
        int $type = Consume::CONSUME_TYPE_DEFAULT,
        array $content = []
    ) :bool {
        $data = [
            'currency' => $currency,
            'player_id' => $playerId,
            'type'  => $type,
            'content'   => json_encode($content),
            'consume_time' => time(),
            'consume' => -$changeMoney,
            'before_consume' => $money,
            'after_consume' => $money - $changeMoney,
            'created_at'    => date('Y-m-d H:i:s', time()),
        ];
        return Consume::create()->data($data)->save();
    }







    /**
     * 配置表-> 特定字段解析返回 function
     *
     * @param string $parserData
     * @param integer $id
     * @return void
     */
    public function configRate($parserData = 'data', $id = 1)
    {
        $config =  Config::create()->where([
            'id'    => $id
        ])->field([$parserData])->get();
        return json_decode($config[$parserData], true);
    }








    /**
     * 玩家任务  function
     *  1.计算自身的比率匹配任务金额,如完成. 则自动计算处理
     * @return integer
     */
    public function rateAuto() : int
    {
        $player = $this->getPlayer();
        $config = Config::create()->where([
            'id'    => Config::CONFIG_EXTENSION_SETTINGS,
        ])->get();
        $configData = json_decode($config['data'], true);
        $selfRate = $this->jwtData['self_rate'] ?? $player['self_rate'];
        if (empty($selfRate)) {
            $rate = (int)$configData['self'] ;
        } else {
            $rate = (int)$selfRate ;
        }
        return $rate;
    }

    //返回ip详细信息 存在的情况下
    public function ipDesc($ip) : string
    {
        try {
            $ipDesc = '';
            $readers = new Reader(GEOIP_DATABASE);
            $record =  $readers->city($ip);
            if (!empty($record->country->isoCode)) {
                $ipDesc .= $record->country->isoCode . '_' ?? '';
                $ipDesc .= $record->country->name  . '_' ?? '';
                $ipDesc .= $record->country->names['zh-CN'] . '_' ?? '';
                $ipDesc .= $record->mostSpecificSubdivision->name . '_' ?? '';
                $ipDesc .= $record->mostSpecificSubdivision->isoCode?? '';
                $ipDesc = $ipDesc;
                return $ip.'||'.$ipDesc;
            }
            return $ip;
        } catch (\Throwable $th) {
            return $ip.$th->getMessage();
        }
    }
}
