<?php
namespace App\Task;

use EasySwoole\Task\AbstractInterface\TaskInterface;
use EasySwoole\EasySwoole\Logger;

use App\Lib\Tool;
use App\Models\Consume;
use App\Models\Player;
use App\Models\PlayerNotify;

/**
 * 用于注册后 赠送 等操作的异步任务 class
 */
class RegisterTask implements TaskInterface
{
    use Tool;
    protected $data;
    //通过构造函数,传入数据,获取该次任务的数据
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function run(int $taskId, int $workerIndex)
    {
        $relations  = !empty($this->data['path']) ?  explode(',', $this->data['path']) : '';

        if (!empty($this->data['path'])) {
            if ($this->data['path'] != SYSTEM_NORMAL) {
                $relations = explode(',', $this->data['path']);
            }
            $relations = (array) $this->data['path'];
        }

        $superior = array_pop($relations);
        $superiorTop = array_pop($relations);
        //新用户注册操作 -> 自身 上级 上上级 ->用户金额操作 & 金币记录
        if (!empty($superior)) {
            if ($this->playerMoneyUpdateUntiy($superior, (int)$this->data['data']['superior'])) {
                $player = $this->getPlayer($superior);
                if ($this->playerMoneyUpdateUntiy(
                    (int)$player['id'],
                    (int)$this->data['data']['superior'],
                )) {
                    $this->playerRecordUntiyAdd(
                        (int)$superior,
                        (int)$player['coin'],
                        (int)$this->data['data']['superior'],
                        Consume::CONSUME_CURRENCY_GOLD,
                        Consume::CONSUME_TYPE_REGISTER_GIVE_SUPERIOR,
                        $this->data
                    );
                }
            }
        }
        if (!empty($superiorTop)) {
            if ($this->playerMoneyUpdateUntiy($superiorTop, (int)$this->data['data']['superior_top'])) {
                $player = $this->getPlayer($superiorTop);
                if ($this->playerMoneyUpdateUntiy(
                    (int)$player['id'],
                    (int)$this->data['data']['superior_top'],
                )) {
                    $this->playerRecordUntiyAdd(
                        (int)$superiorTop,
                        (int)$player['coin'],
                        (int)$this->data['data']['superior_top'],
                        Consume::CONSUME_CURRENCY_GOLD,
                        Consume::CONSUME_TYPE_REGISTER_GIVE_SUPERIOR,
                        $this->data
                    );
                }
            }
        }

        if (isset($this->data['new_user'])) {
            $player = $this->getPlayer($this->data['new_user']);
            if ($this->playerMoneyUpdateUntiy(
                (int)$player['id'],
                (int)$this->data['data']['new_user']
            )) {
                $this->playerRecordUntiyAdd(
                    (int)$player['id'],
                    (int)$player['coin'],
                    (int)$this->data['data']['new_user'],
                    Consume::CONSUME_CURRENCY_GOLD,
                    Consume::CONSUME_TYPE_REGISTER_GIVE,
                    $this->data
                );
            }
            $playerNotify = [
                'announce_id'    => PlayerNotify::ANNOUNCE_ID_REGISTER_PUSH,
                'recipient_id'   => (int)$player['id'],
                'created_at'    => date('Y-m-d H:i:s', time()),
                'content'   => json_encode([
                    'title' => 'New message',
                    'content'=>'The biggest opportunity in Internet history in 2020,realize your dream Give you '.(int)$this->data['data']['new_user'].' coins',
                    'created_at'=> date("Y-m-d H:i:s"),
                    'coin'  => (int)$this->data['data']['new_user'],
                    'announce_id'=>PlayerNotify::ANNOUNCE_ID_REGISTER_PUSH
                    ]),
            ];
            PlayerNotify::create()->data($playerNotify)->save();
        }
        //2.其它业务扩展 notifycation


        Logger::getInstance()->log('RegisterTask-->  TASK_ID-->'.$taskId.'  WORKER_INDEX-->'.$workerIndex.' data-->'.json_encode($this->data));
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
    }
}
