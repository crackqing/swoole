<?php
namespace App\Task;

use EasySwoole\Task\AbstractInterface\TaskInterface;
use EasySwoole\EasySwoole\Logger;

use App\Lib\Tool;
use App\Models\Consume;
use App\Models\Reward;

/**
 * 累积邀请人数得现金 异步任务 class
 */
class InvitesCash implements TaskInterface
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
        //config -> 累积邀请人数得现金 达到对应人数 直接赠送操作
        foreach ($this->data['data'] as $k => $v) {
            $player = $this->getPlayer($this->data['parent_id']);

            //满足条件,直接赠送 或者增加领取记录表. 用于用户手动领取奖励表
            if ($v['value'] == $this->data['num']) {
                //领取奖励表
                $data = [
                    'player_id' => $this->data['parent_id'],
                    'type'  => Reward::REWARD_TYPE_INVITE_CASH,
                    'reward'    => (int) $v['desc'],
                    'add_time'    => strtotime('today'),
                    'reward_currency'   => Consume::CONSUME_CURRENCY_CASH,
                    'created_at'    => date('Y-m-d H:i:s', time()),
                ];
                Reward::create()->data($data)->save();
                




                //自动发放
                // $playerUntiy =  $this->playerMoneyUpdateUntiy(
                //     (int)$this->data['parent_id'],
                //     (int)$v['desc'],
                //     [],
                //     'inc',
                //     'money'
                // );
                // if ($playerUntiy) {
                //     $this->playerRecordUntiyAdd(
                //         (int)$this->data['parent_id'],
                //         (int)$player['money'],
                //         (int)$v['desc'],
                //         Consume::CONSUME_CURRENCY_CASH,
                //         Consume::CONSUME_TYPE_INVITES_CASH,
                //         $v,
                //     );
                // }
            }
        }
        Logger::getInstance()->log('InvitesCash_TASK_ID-->'.$taskId.'  WORKER_INDEX-->'.$workerIndex.' data-->'.json_encode($this->data));
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
    }
}
