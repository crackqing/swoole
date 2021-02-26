<?php
namespace App\Crontab;

use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use EasySwoole\EasySwoole\Logger;

use App\Models\DailyBonus;
use EasySwoole\Mysqli\QueryBuilder;

//定时执行->更新每日签到任务状态
class CrontabDailyBonus extends AbstractCronTask
{
    public static function getRule(): string
    {
        return CRONTAB_REFRESH;
    }

    public static function getTaskName(): string
    {
        return  'daily_bonus';
    }

    public function run(int $taskId, int $workerIndex)
    {
        //1.如当天没有完成任务.直接删除记录重新从第一天算起  2.如完成则次数累加1,更改状态
        $daily =  DailyBonus::create()->all();
        foreach ($daily as $v) {
            if ($v->status == 0) {
                if (CRONTAB_STATUS) { //线上需要删除处理
                    DailyBonus::create()->destroy($v->id);
                }
            } else {
                DailyBonus::create()->update([
                    'frequency' => QueryBuilder::inc(1),
                    'status'    => 0,
                    'updated_at' => date('Y-m-d H:i:s', time()),
                ], [
                    'id'    => $v->id
                ]);
            }
        }
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        Logger::getInstance()->info($throwable->getMessage().'CronabDailyBonus->TASK_ID->'.$taskId.' WORKER_INDEX->'.$workerIndex);
    }
}
