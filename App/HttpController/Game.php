<?php
namespace App\HttpController;

//REQUEST VALIDATE
use EasySwoole\Validate\Validate;

//ORM
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;

use App\Models\Player;
use App\Models\Game as GameModels;
use App\Models\GameRecord;
use App\Models\Consume;

class Game extends Base
{
    /**
     * @api {GET} /api/v1/game/config game_config
     *
     * @apiVersion 1.0.0
     * @apiName game/config 游戏配置
     *
     * @apiDescription 当前游戏配置信息,概率等重要信息不返回.
     *
     * @apiParam {Number} game_id  REMARK -> 游戏id 默认 1 => 大转盘   RULE -> required
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": "200",
     *       "result": {
     *          "name" : "游戏名称"
     *          "consumption" : "1000" 每次游玩需要花费的金币
     *          "content" : {不同游戏数值不同,需沟通设置}
     *       }
     *       "msg": "success"
     *     }
     * @apiGroup GAME
     */
    public function config()
    {
        // \EasySwoole\EasySwoole\Logger::getInstance(new \App\Log\ProcessHandel())->log('RebateProcess--> RATE->');
        try {
            $valitor = new Validate();
            $valitor->addColumn('game_id')->required('game_id is empty!');
            if ($valitor->validate($this->params) != true) {
                return $this->responseWirteJsonError($valitor->getError()->__toString());
            }
            $result =  GameModels::create()->where([
                'id'    => $this->params['game_id'] ?? self::NORMAL
                ])
            ->get();
            if (empty($result)) {
                return $this->responseWirteJsonSuccess($result);
            }
            $arr = [];
            $arr['name'] = $result['name'];
            $arr['consumption'] = $result['consumption'];
            foreach ($result['content'] as $k => $v) {
                $arr['content'][$k]['key'] = $v['key'];
                $arr['content'][$k]['value'] = $v['value'];
                $arr['content'][$k]['reward'] = $v['reward'];
                $arr['content'][$k]['diskColor'] = $v['diskColor'];
                $arr['content'][$k]['image'] = APP_HOST.'india/'.$v['image'];
            }
            // foreach ($result['restrict'] as $k => $v) {
            //     $arr['restrict'][$k]['key'] = $v['key'];
            //     $arr['restrict'][$k]['value'] = $v['value'];
            //     $arr['restrict'][$k]['desc'] = $v['desc'];
            // }
            return $this->responseWirteJsonSuccess($arr);
        } catch (\Throwable $th) {
            return $this->responseWirteJsonError($th->getMessage());
        }
    }

    /**
     * @api {GET} /api/v1/game/turntable game_turntabel
     *
     * @apiVersion 1.0.0
     * @apiName game_turntabel
     *
     * @apiDescription 大转盘
     * @apiGroup GAME
     */
    public function turntable()
    {
        //大转盘 游戏业务逻辑处理
        $lottery =  [];
        $arr = [];
        $refreshLottery = 0;
        try {
            DbManager::getInstance()->startTransaction();

            $player = $this->getPlayer();
            $result =  GameModels::create()->where([
                'id'    => GameModels::GMAE_TURNTABLE
                ])
            ->get();
            //First ->  金币不足,则提示否则自动减去金币操作.
            if ((int) $player['coin'] >= (int) $result['consumption']) {
                //1.减去玩家金币 & 新加抽奖机会次数
                $playerUpdaet = $this->playerMoneyUpdateUntiy(
                    $this->jwtData['id'],
                    $result['consumption'],
                    [
                        'lottery' => QueryBuilder::inc(self::NORMAL),
                    ],
                    'dec'
                );
                if ($playerUpdaet) {
                    $this->playerRecordUntiyReduce(
                        (int)$this->jwtData['id'],
                        (int)$player['coin'],
                        $result['consumption'],
                        Consume::CONSUME_CURRENCY_GOLD,
                        Consume::CONSUME_TYPE_LOTTERY_REFRESH
                    );
                    $refreshLottery++;
                }
            }
            if ((int) $player['coin'] < (int) $result['consumption']) {
                DbManager::getInstance()->commit();
                return $this->responseWirteJsonError('The current player\'s gold coin balance is insufficient and cannot be refreshed');
            }
            if ((int) ($player['lottery'] + $refreshLottery) <= self::PLACEHOLDER) {
                DbManager::getInstance()->commit();
                return $this->responseWirteJsonError('The lottery chance for the day has ended');
            }
            //Game 概率算法处理. 区间取值,只支持两条限制处理
            $proArr = $result['content'];
            $restrict = $result['restrict'];
            $playerGameCount = GameRecord::create()->where(['player_id' => $this->jwtData['id']])->count();
            foreach ($proArr as $v) {
                $arr[$v['key']] = $v['desc'];
            }
            //restrict 规则限制处理 达到一定次数的玩家才可获取后面对应的奖品
            // if ($playerGameCount < $restrict[0]['key']) { //50
            //     # code...
            // }
            // var_dump($playerGameCount, $arr);
            $rid = $this->getRand($arr);
            if ($this->getRand($arr) ==  -1) {
                throw new \Exception("Error Processing Request", 1);
            }
            $lottery = $proArr[$rid - 1];
            if ($lottery['value'] == 'Free again') {
                //返回钱数,不做记录
                Player::create()->update([
                    'coin'  => QueryBuilder::inc($result['consumption'])
                ], [
                    'id'    => $this->jwtData['id']
                ]);

                DbManager::getInstance()->commit();

                
                return $this->responseWirteJsonSuccess(array_merge($lottery, ['coin' => $player['coin'],'RS' => $player['money'],'lottery' => $player['lottery']]));
            }
            unset($lottery['desc']); //概率删除,不需要显示在页面上

            $lottery['phone'] = $this->jwtData['phone'] ?? '';
            if ($lottery['value'] != 'NotWinning') {
                $lotteryRecord = [
                    'game_id'   => self::NORMAL,
                    'player_id' => $this->jwtData['id'],
                    'prize' => json_encode($lottery),
                    'add_time'  => strtotime('today'),
                    'created_at'    => date('Y-m-d H:i:s', time()),
                ];
                GameRecord::create()->data($lotteryRecord)->save(); //抽奖记录
            }

            //金币记录 & 与返回中奖推送通知 Task websocket
            if ($lottery['reward'] != self::PLACEHOLDER) {
                $playerUpdaet = $this->playerMoneyUpdateUntiy(
                    $this->jwtData['id'],
                    $lottery['reward'],
                    [
                        'lottery' => QueryBuilder::dec(1),
                    ],
                    'inc',
                    'money'
                );
                if ($playerUpdaet) {
                    //更新coin
                    $player = $this->getPlayer();
                    $this->playerRecordUntiyAdd(
                        (int)$this->jwtData['id'],
                        (int)$player['money'],
                        (int)$lottery['reward'],
                        Consume::CONSUME_CURRENCY_CASH,
                        Consume::CONSUME_TYPE_LOTTERY,
                        $lottery,
                    );
                }
            } else {
                $playerUpdaet = $this->playerMoneyUpdateUntiy(
                    $this->jwtData['id'],
                    $lottery['reward'],
                    [
                        'lottery' => QueryBuilder::dec(1),
                    ]
                );
            }
            $player = $this->getPlayer();
            return $this->responseWirteJsonSuccess(array_merge($lottery, ['coin' => $player['coin'],'RS' => $player['money'],'lottery' => $player['lottery']]));
        } catch (\Throwable $th) {
            DbManager::getInstance()->rollback();
            return $this->responseWirteJsonError($th->getMessage());
        } finally {
            DbManager::getInstance()->commit();
        }
    }
}
