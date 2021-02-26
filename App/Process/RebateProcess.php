<?php
namespace App\Process;

use EasySwoole\Component\Process\AbstractProcess;

use Swoole\Process;

use App\Models\TaskUser;
use App\Models\Task;
use App\Models\Player;
use App\Models\Config;
use App\Models\Consume;
use App\Models\DailyTotal;
use App\Models\DailyTotalDetailed;

use EasySwoole\ORM\DbManager;

use App\Lib\Tool;
use EasySwoole\Mysqli\QueryBuilder;

#LOG
use App\Log\ProcessHandel;

#三层返佣金,如个人未配置. 则取系统配置.
class RebateProcess extends AbstractProcess
{
    use Tool;
    protected function run($arg)
    {
        go(function () {
            while (true) {
                try {
                    $rate = ['self' => 0,'superior' => 0,'superior_top' => 0];
                    $changeMoney = 0;
                    $baseRate = 100;
                    DbManager::getInstance()->startTransaction();
                    $taskUser =  TaskUser::create()->where([
                        'status'    => TaskUser::TASK_STATUS_SUCCESS,
                        'flag'  => 0,
                    ])->get();
                    $today = strtotime('today');

                    if (!empty($taskUser)) {
                        $player = Player::create()->where([
                            'id'    => $taskUser['player_id']
                        ])
                        ->get();
            
                        $task = Task::create()->where([
                            'id' => $taskUser['task_id']
                        ])
                        ->field(['id','total_price'])
                        ->get();
            
                        if (empty($player['self_rate'])) {
                            $rate =  $this->configRate('data', Config::CONFIG_EXTENSION_SETTINGS);
                        } else {
                            $rate['self'] = $player['self_rate'];
                            $rate['superior'] = $player['p_rate'];
                            $rate['superior_top'] = $player['p_p_rate'];
                        }
                        $totalPrice  =  (int)$task['total_price'] ?? 0;
            
                        //计算公式 $task['total_price'] * $rate['self']
                        if (isset($player['id']) && $player['id'] != 1 && $totalPrice != 0) {
                            $changeMoney = $totalPrice * ($rate['self'] / $baseRate);
                            if ($this->playerMoneyUpdateUntiy(
                                (int)$player['id'],
                                $changeMoney
                            )) {
                                $this->playerRecordUntiyAdd(
                                    (int)$player['id'],
                                    $player['coin'],
                                    $changeMoney,
                                    Consume::CONSUME_CURRENCY_GOLD,
                                    Consume::CONSUME_TYPE_TASK_REBATE,
                                );
                            }
                        }
                        //second_coin 计算当天下级返的金币数 也就是佣金
                        if (isset($player['parent_id']) && $player['parent_id'] != 1  && $totalPrice != 0) {
                            $changeMoney = $totalPrice * ($rate['superior'] / $baseRate);
                            $parentPlayer = $this->getPlayer($player['parent_id']);
                            if (!empty($parentPlayer)) {
                                if ($this->playerMoneyUpdateUntiy(
                                    (int)$player['parent_id'],
                                    $changeMoney
                                )) {
                                    $this->playerRecordUntiyAdd(
                                        (int)$player['parent_id'],
                                        $parentPlayer['coin'],
                                        $changeMoney,
                                        Consume::CONSUME_CURRENCY_GOLD,
                                        Consume::CONSUME_TYPE_TASK_REBATE,
                                    );
                                    //额外累计 & 具体的贡献
                                    DailyTotal::create()->update([
                                        'second_coin'   => QueryBuilder::inc($changeMoney),
                                    ], [
                                        'player_id' => (int)$player['parent_id'],
                                        'daily_time'    => $today,
                                    ]);
                                    //具体的人返记录,前端显示
                                    $detailed =  DailyTotalDetailed::create()->where([
                                        'player_id' => (int)$player['parent_id'],
                                        'sub_id'    => $taskUser['player_id'],
                                        'daily_time'    => $today,
                                    ])->get();
                                    if (empty($detailed)) {
                                        $detailedData = [
                                            'player_id' => (int)$player['parent_id'],
                                            'sub_id'    => $taskUser['player_id'],
                                            'level' => 2,
                                            'coin'  => $changeMoney,
                                            'daily_time'    => $today,
                                            'created_at'    => date("Y-m-d H:i:s")
                                        ];
                                        DailyTotalDetailed::create()->data($detailedData)->save();
                                    } else {
                                        DailyTotalDetailed::create()->update([
                                            'coin'   => QueryBuilder::inc($changeMoney),
                                        ], [
                                            'player_id' => (int)$player['parent_id'],
                                            'sub_id'    => $taskUser['player_id'],
                                            'daily_time'    => $today,
                                        ]);
                                    }
                                }
                            }
                        }
                        //three_coin 计算当天下级返的金币数 也就是佣金
                        if (isset($player['p_pid']) && $player['p_pid'] != 1  && $totalPrice != 0) {
                            $changeMoney = $totalPrice * ($rate['superior_top'] / $baseRate);
                            $pparentPlayer = $this->getPlayer($player['p_pid']);
                            if (!empty($pparentPlayer)) {
                                if ($this->playerMoneyUpdateUntiy(
                                    (int)$player['p_pid'],
                                    $changeMoney
                                )) {
                                    $this->playerRecordUntiyAdd(
                                        (int)$player['p_pid'],
                                        $pparentPlayer['coin'],
                                        $changeMoney,
                                        Consume::CONSUME_CURRENCY_GOLD,
                                        Consume::CONSUME_TYPE_TASK_REBATE,
                                    );
                                    //额外累计
                                    DailyTotal::create()->update([
                                        'three_coin'   => QueryBuilder::inc($changeMoney),
                                    ], [
                                        'player_id' => (int)$player['p_pid'],
                                        'daily_time'    => $today,
                                    ]);
                                    //具体的人返记录,前端显示
                                    $detailed =  DailyTotalDetailed::create()->where([
                                        'player_id' => (int)$player['p_pid'],
                                        'sub_id'    => $taskUser['player_id'],
                                        'daily_time'    => $today,
                                    ])->get();
                                    if (empty($detailed)) {
                                        $detailedData = [
                                            'player_id' => (int)$player['p_pid'],
                                            'sub_id'    => $taskUser['player_id'],
                                            'level' => 3,
                                            'coin'  => $changeMoney,
                                            'daily_time'    => $today,
                                            'created_at'    => date("Y-m-d H:i:s")
                                        ];
                                        DailyTotalDetailed::create()->data($detailedData)->save();
                                    } else {
                                        DailyTotalDetailed::create()->update([
                                            'coin'   => QueryBuilder::inc($changeMoney),
                                        ], [
                                            'player_id' => (int)$player['p_pid'],
                                            'sub_id'    => $taskUser['player_id'],
                                            'daily_time'    => $today,
                                        ]);
                                    }
                                }
                            }
                        }
                        TaskUser::create()->update([
                            'flag'  => 1
                        ], [
                            'id'    => $taskUser['id']
                        ]);
                        \EasySwoole\EasySwoole\Logger::getInstance(new ProcessHandel())->log('RebateProcess--> RATE->'.json_encode($rate).'  player->'.json_encode($player));
                    }
                } catch (\Throwable $th) {
                    DbManager::getInstance()->rollback();
                    \EasySwoole\EasySwoole\Logger::getInstance(new ProcessHandel())->log('RebateProcess-> THROWABLE'.$th->getMessage());
                } finally {
                    DbManager::getInstance()->commit();
                }
                \co::sleep(5);
            }
        });
    }


    protected function onPipeReadable(Process $process)
    {
        /*
         * 该回调可选
         * 当有主进程对子进程发送消息的时候，会触发的回调，触发后，务必使用
         * $process->read()来读取消息
         */
    }

    protected function onShutDown()
    {
        /*
         * 该回调可选
         * 当该进程退出的时候，会执行该回调
         */
    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        /*
         * 该回调可选
         * 当该进程出现异常的时候，会执行该回调
         */
    }
}
