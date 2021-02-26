<?php


namespace EasySwoole\EasySwoole;

#CORE EASYSWOOLE
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\Logger;

#HTTP SERVER
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

#DB ORM
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;

#PROCESS 后台操作状态,统一处理进程. 方便汇总数据
use EasySwoole\Component\Process\Manager;
use App\Process\RebateProcess; //返利统一计算操作
use App\Process\PlayerProcess; //玩家统一金额统一操作
use App\Process\WithdrawProcess; //提现统一操作操作

//CRONTAB
use EasySwoole\EasySwoole\Crontab\Crontab;
use App\Crontab\CrontabDailyBonus;

#TASK
use App\Task\ApiStatisticsTask;

define('REDIS_FIELD_SMS', 'sms:');

#QUEUE 操作
define('UNITY_QUEUE_TIMEOUT', 3);
define('UNITY_PLAYER_COIN', 'QUEUE:PLAYER_COIN:'); //队列->玩家金币操作
define('UNITY_PLAYER_MONEY', 'QUEUE:PLAYER_MONEY:'); //队列->玩家现金操作
#LIMIT
define('SMS_SNED_PHONE_LIMIT', 120); //发送时间间隔为90秒,的redis值记录


#DEFUALT
define('SYSTEM_PLACEHOLDER', 0);
define('SYSTEM_NORMAL', 1);
define('SYSTEM_SECOND', 2);

define('GEOIP_DATABASE', EASYSWOOLE_ROOT.'/public/GeoLite2-City.mmdb'); //IP数据库,手动更新每月
/**
 * 集群存在问题
 *  1.进程会启用多个不同服务器下,需要db加锁或者redis加锁来限制对应执行
 *  2.图片还是存在本机上，需要使用cos或者单独一台图片服务器来处理
 *  3.定时服务需要指定一台来运行,而不是多台
 *
 */
class EasySwooleEvent implements Event
{
    public static $endTimeMs = 0;
    public static $startTimeMs = 0;

    //SYNC同步BASE 用于跳过对应路由检测
    protected static $skipCheck = [
        '/api/v1/user/login',
        '/api/v1/user/register',
        '/api/v1/external/bind',
        '/api/v1/external/getBind',
        '/api/v1/services/payNotify',
        '/api/v1/services/sms',
        '/api/v1/user/refresh'
    ];

    public static function initialize()
    {
        $instance = \EasySwoole\EasySwoole\Config::getInstance();
        $redisAuth =  $instance->getConf('REDIS1.auth');
        $redisHost =  $instance->getConf('REDIS1.host');
        $redisProt =  $instance->getConf('REDIS1.port');
        
        //图片地址,特定模型显示的是后台地址  其它是本项目地址
        $domain_api = $instance->getConf('domain_api');
        $domain_admin = $instance->getConf('domain_admin');
        $crontabDefine = $instance->getConf('crontab_refresh');
        $crontabStatus = $instance->getConf('crontab_status');
        $debug = $instance->getConf('debug');
        $nginxProxy = $instance->getConf('nginx_proxy');
        $push_socket = $instance->getConf('push_socket');

        define('DOMAIN_API', $domain_api);
        define('APP_HOST', $domain_api);
        define('DOMAIN_ADMIN', $domain_admin);
        
        define('CRONTAB_REFRESH', $crontabDefine);
        define('CRONTAB_STATUS', $crontabStatus);
        
        define('APP_DEBUG_PRODUCT', $debug);
        define('APP_NGINX_PROXY', $nginxProxy);
        define('APP_WEB_SOCKET_PUSH', $push_socket);
        //本地调试时,单台. 线上使用NGINX反代处理
        if (APP_NGINX_PROXY) {
            //自定义TOKEN字段  NGINX 与 项目代码都需要加上
            \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response) {
                $response->withHeader('Access-Control-Allow-Origin', '*');
                $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
                $response->withHeader('Access-Control-Allow-Credentials', 'true');
                $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With,token');
                if ($request->getMethod() === 'OPTIONS') {
                    $response->withStatus(\EasySwoole\Http\Message\Status::CODE_OK);
                    return false;
                }
                return true;
            });
        }
        //TIME_ZONE
        date_default_timezone_set('Asia/Shanghai');

        //ORM POOL
        $config = new \EasySwoole\ORM\Db\Config(Config::getInstance()->getConf('MYSQL'));
        $config->setGetObjectTimeout(3.0); //设置获取连接池对象超时时间
        $config->setIntervalCheckTime(30*1000); //设置检测连接存活执行回收和创建的周期
        $config->setMaxIdleTime(15); //连接池对象最大闲置时间(秒)
        $config->setMaxObjectNum(32); //设置最大连接池存在连接对象数量
        $config->setMinObjectNum(5); //设置最小连接池存在连接对象数量
        $config->setAutoPing(5); //设置自动ping客户端链接的间隔
        DbManager::getInstance()->addConnection(new Connection($config));

        //REDIS POOL
        $redisPoolConfig =\EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig([
            'host'  => $redisHost,
            'auth'  => $redisAuth,
            'port'  => $redisProt,
        ]), 'redis');
        $redisPoolConfig->setMinObjectNum(5);
        $redisPoolConfig->setMaxObjectNum(20);

        $skipCheck = self::$skipCheck;
        //新版本 3.4.* onRequest
        // \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_ON_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response) {
        //     return true;
        // });

        //新版本 3.4.* afterRequest
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (\EasySwoole\Http\Request $request, \EasySwoole\Http\Response $response) use ($skipCheck) {
            if (!in_array($request->getServerParams()['request_method'], $skipCheck)) {
                $swooleParams = $request->getSwooleRequest() ;
                $endTime =  microtime(true);
                $ip = '127.0.0.1';
                if (isset($swooleParams->header['x-real-ip']) ||
                    isset($swooleParams->header['x-forwarded-for'])) {
                    $ip = $swooleParams->header['x-real-ip'] ?? $swooleParams->header['x-forwarded-for'];
                } else {
                    $ip = $request->getServerParams()['remote_addr'];
                }
                $data = [
                    'request_method'    => $request->getServerParams()['request_method'] ?? 'GET',
                    'query_string'  => $request->getServerParams()['query_string'] ?? '',
                    'request_uri'  => $request->getServerParams()['request_uri'] ?? '',
                    'request_time_float'  => $request->getServerParams()['request_time_float'] ?? '',
                    'remote_addr'  => $ip,
                    'user-agent'  => $swooleParams->header['user-agent'] ?? '',
                    'response_time_float'  => $endTime,
                    'time_x`'  =>  round($endTime - $request->getServerParams()['request_time_float'], 3).'ms',
                    'created_at'    => date('Y-m-d H:i:s')
                ];
                // Logger::getInstance()->info('data---->'.json_encode($data).'swooleParams'.json_encode($swooleParams));
                $task = \EasySwoole\EasySwoole\Task\TaskManager::getInstance();
                $task->async(new ApiStatisticsTask($data));
            }
        });
    }
    //TODO:单台处理 crontab 其它集群时处理分布式锁或者原子锁处理
    //TODO:每隔6小时restart重启服务,防止异常或者内存泄露等情况.
    public static function mainServerCreate(EventRegister $register)
    {
        //HOT-RELOAD DEBUG MODE 开发模式
        $hotReloadTrue =  Config::getInstance()->getConf('hot_reload');
        if ($hotReloadTrue) {
            $hotReloadOptions = new \EasySwoole\HotReload\HotReloadOptions;
            $hotReload = new \EasySwoole\HotReload\HotReload($hotReloadOptions);
            $hotReloadOptions->setMonitorFolder([EASYSWOOLE_ROOT . '/App']);
    
            $server = ServerManager::getInstance()->getSwooleServer();
            $hotReload->attachToServer($server);
        }
        //CRONTAB
        Crontab::getInstance()->addTask(CrontabDailyBonus::class); //每日签到自动累加或者重新签到
        //QUEUE 队列服务处理 审核,增加减少金币统一操作,提现=操作. 统一处理



        //WEBSOCKET-->task_socket另开项目


        //PROCESS
        $processConfig= new \EasySwoole\Component\Process\Config();
        $processConfig->setProcessName('process_task');//设置进程名称
        $processConfig->setProcessGroup('process_task');//设置进程组
        $processConfig->setArg(['k'=> 'v']);//传参 ->代理池子

        $processConfig->setRedirectStdinStdout(false);//是否重定向标准io
        $processConfig->setPipeType($processConfig::PIPE_TYPE_SOCK_DGRAM);//设置管道类型
        $processConfig->setEnableCoroutine(true);//是否自动开启协程
        $processConfig->setMaxExitWaitTime(5);//最大退出等待时间


        Manager::getInstance()->addProcess(new RebateProcess($processConfig));
        Manager::getInstance()->addProcess(new PlayerProcess($processConfig));
        if (APP_DEBUG_PRODUCT) { //线上才开启的任务处理
            Manager::getInstance()->addProcess(new WithdrawProcess($processConfig));
        }
    }
    public static function onRequest(Request $request, Response $response) :bool
    {
        return true;
    }
    
    public static function afterRequest(Request $request, Response $response) : void
    {
    }
}
