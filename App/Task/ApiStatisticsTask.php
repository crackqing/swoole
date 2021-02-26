<?php
namespace App\Task;

use EasySwoole\Task\AbstractInterface\TaskInterface;
use App\Models\ApiStatistics;

use GeoIp2\Database\Reader;
use Jenssegers\Agent\Agent;

class ApiStatisticsTask implements TaskInterface
{
    protected $data;
    //通过构造函数,传入数据,获取该次任务的数据
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function run(int $taskId, int $workerIndex)
    {
        //解析TOKEN,用于识别具体用户ID存放,用于搜索

        //更新UA头具体信息
        $ua  = $this->data['user-agent'];
        $agent = new Agent();
        $agent->setUserAgent($ua);
        $uaString = '';
        if (!empty($agent->device())) {
            $uaString .= 'device:'.$agent->device().' ';
        }
        if (!empty($agent->platform())) {
            $uaString .= 'platform:'.$agent->platform().' ';
        }
        if (!empty($agent->browser())) {
            $uaString .= 'browser:'.$agent->browser().' ';
        }
        if (!empty($uaString)) {
            $this->data['user-agent'] = $uaString;
        }
        try {
            //IP详细添加 & 登录IP &注册IP 直接在后面查询对应显示就行. TEST
            $ipDesc = '';
            $reader = new Reader(GEOIP_DATABASE);
            $record =  $reader->city($this->data['remote_addr']);
            if (!empty($record->country->isoCode)) {
                $ipDesc .= $record->country->isoCode . '_' ?? '';
                $ipDesc .= $record->country->name  . '_' ?? '';
                $ipDesc .= $record->country->names['zh-CN'] . '_' ?? '';
                $ipDesc .= $record->mostSpecificSubdivision->name . '_' ?? '';
                $ipDesc .= $record->mostSpecificSubdivision->isoCode?? '';
                $this->data['geoip'] = $ipDesc;
            }
            ApiStatistics::create()->data($this->data)->save();
        } catch (\Throwable $th) {
            ApiStatistics::create()->data($this->data)->save();
        }
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
    }
}
