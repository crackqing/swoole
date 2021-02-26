<?php
namespace App\Task;

use EasySwoole\Task\AbstractInterface\TaskInterface;
use EasySwoole\EasySwoole\Logger;

use App\Lib\Tool;
use App\Models\PlayerNotify;
use App\Services\PushSocket;

/**
 * 用于推送检测,全局使用
 *  1.目前只用于注册弹窗使用
 */
class PushTask implements TaskInterface
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
        //PUSH
        $notify = PlayerNotify::create()->where([
            'announce_id'   => PlayerNotify::ANNOUNCE_ID_REGISTER_PUSH
        ])->get();
        if (!empty($notify)) {
            $pushSocket = new PushSocket();
            $pushData = [
                'controller'    => 'Player',
                'action'    => 'push',
                'param'    => [
                    'player_id' => $this->data['player_id'],
                    'announce_id'   => PlayerNotify::ANNOUNCE_ID_REGISTER_PUSH,
                ]
            ];
            $pushSocket->push($pushData['controller'], $pushData['action'], $pushData['param']);
        }
        Logger::getInstance()->log('PushTask-->  TASK_ID-->'.$taskId.'  WORKER_INDEX-->'.$workerIndex.' data-->'.json_encode($this->data));
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
    }
}
