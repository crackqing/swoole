<?php


namespace EasySwoole\EasySwoole;

use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

#DB ORM
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;

#WEB SOCKET
use App\Parser\WebsocketParser;

#ORM
use App\Models\Player;
use App\Models\PlayerNotify;

//websocket 推送处理,与9501 配置参数一致 SYNC 9501配置与参数 不单独定义一套处理
class EasySwooleEvent implements Event
{
    //API
    const API_STATUS_SUCCESS_CODE = 200;
    const API_STATUS_ERROR_CODE = 400;

    const API_SUCCESS_MSG = 'success';
    const API_ERROR_MSG = 'error';
    const API_NO_PERMISSION = 'no_permission';
    
    public static function initialize()
    {
        $instance = \EasySwoole\EasySwoole\Config::getInstance();
        $redisAuth =  $instance->getConf('REDIS1.auth');
        $redisHost =  $instance->getConf('REDIS1.host');
        $redisProt =  $instance->getConf('REDIS1.port');
     
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
        
        
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        //全局定时器 Timer
        $register->add(EventRegister::onWorkerStart, function (\swoole_server $server, $workerId) {
            if ($workerId == 0) {
                \EasySwoole\Component\Timer::getInstance()->loop(10 * 1000, function () {
                    \EasySwoole\Component\Timer::getInstance()->after(10 * 1000, function () {
                        $redis = \EasySwoole\RedisPool\RedisPool::defer('redis');
                        $PlayerNotify = PlayerNotify::create()->where([
                            'announce_id'   => PlayerNotify::ANNOUNCE_ID_REGISTER_PUSH,
                            'state' => 0,
                        ])->all();
                        foreach ($PlayerNotify as $k => $v) {
                            $player = Player::create()
                                ->where([
                                    'id'    => $v->recipient_id,
                                ])->field(['phone'])
                                ->get();
                            $redisReuslt =  $redis->hMGet(Player::REDIS_PLAYER_HMSET.$player['phone'], ['fd']);
                            if (!empty($redisReuslt[0])) {
                                Logger::getInstance()->console("TIMER->2SEC->resultFd->".$redisReuslt[0]);
                                $serverFd = ServerManager::getInstance()->getSwooleServer();
                                if ($serverFd->push($redisReuslt[0], $v->content)) {
                                    PlayerNotify::create()->update([
                                        'state' => 1,
                                    ], [
                                        'id'    => $v->id
                                    ]);
                                }
                            }
                        }
                    });
                });
            }
        });


        //WEB SOCKET
        $swooleConfig = new \EasySwoole\Socket\Config();
        $swooleConfig->setType($swooleConfig::WEB_SOCKET);
        $swooleConfig->setParser(WebsocketParser::class);
        $dispatcher = new \EasySwoole\Socket\Dispatcher($swooleConfig);
        
        $swooleConfig->setOnExceptionHandler(function (\Swoole\Server $server, \Throwable $throwable, string $raw, \EasySwoole\Socket\Client\WebSocket $client, \EasySwoole\Socket\Bean\Response $response) {
            $response->setMessage('system error!');
            $response->setStatus($response::STATUS_RESPONSE_AND_CLOSE);
        });

        //LOCALHOST -->ws://192.168.3.34:9601/?token=xx&phone=1111  => code result msg   $request->server | fd | get
        //PRODUCT --> wss://api.1457889563.xyz/?token=xxx&phone=1231145690ss 线上返代处理

        $register->set($register::onOpen, function ($ws, $request) {
            $redis = \EasySwoole\RedisPool\RedisPool::defer('redis');
            if (!isset($request->get['token']) && !isset($request->get['phone'])) {
                $ws->push($request->fd, json_encode([
                    'code'  => self::API_STATUS_ERROR_CODE,
                    'msg'   => self::API_ERROR_MSG,
                    'result'    =>[],
                ]));
                return;
            }
            //EASYSWOOLE_API_SOCKET SHA1-256  自定义后台与API接口推送 不走验证处理
            if ($request->get['token'] == 'bbaf038d0943de8319727c6ab8d024bfc3ef41634e504a3aba1f1e96653d8d48') {
                echo 'CLIENT_API_REQUEST->'.$request->fd."\n";
            //直接推送{"controller":"Index","action":"index","params" : {"params":...}} 定义好推送处理  不进行验证
            } else {
                $player =  $redis->hMGet(Player::REDIS_PLAYER_HMSET.$request->get['phone'], ['token','expiration','id']);
                if ($player[0] != $request->get['token'] || $player[1] <= time()) {
                    $ws->push($request->fd, json_encode([
                        'code'  => self::API_STATUS_ERROR_CODE,
                        'msg'   => self::API_ERROR_MSG,
                        'result'    =>[],
                    ]));
                    return;
                }
                $redis->hMSet(Player::REDIS_PLAYER_HMSET.$request->get['phone'], [
                    'fd'    => $request->fd,
                ]);
                Player::create()->update([
                    'is_online' => 1
                ], [
                    'phone' => $request->get['phone']
                ]);
                $mssageCount =  PlayerNotify::create()->where([
                    'recipient_id'  => $player[2],
                ])->count();
                $data = [
                    'code'  => self::API_STATUS_SUCCESS_CODE,
                    'msg'   => self::API_SUCCESS_MSG,
                    'result'    => [
                        'message_count' => $mssageCount, //未读消息
                    ],
                ];
                $redis->set('SCOKET:FD:'.$request->fd, $request->get['phone']);

                echo 'CLIENT_REQUEST-> FD-->'.$request->fd.'  phone-->'.$request->get['phone'];

                $ws->push($request->fd, json_encode($data));
            }
        });

        //ROUTER 路由定义处理
        $register->set($register::onMessage, function (\Swoole\WebSocket\Server $server, \Swoole\WebSocket\Frame $frame) use ($dispatcher) {
            echo "Message: {$frame->data}\n";
            $dispatcher->dispatch($server, $frame->data, $frame); // $server->push($frame->fd, "server: {$frame->data}");
        });
    

        //处理用户下线的处理
        $register->set($register::onClose, function ($ws, $fd) {
            $redis = \EasySwoole\RedisPool\RedisPool::defer('redis');
            $phone = $redis->get('SCOKET:FD:'.$fd);
            if (!empty($phone)) {
                $player = Player::create()->where([
                    'phone' => $phone
                ])->get();

                Player::create()->update([
                    'is_online' => 0
                ], [
                    'id'    => $player['id']
                ]);
                $redis->del('SCOKET:FD:'.$fd);
                $redis->hDel(Player::REDIS_PLAYER_HMSET.$player['phone'], 'fd');
            }
            echo "client-{$fd} is closed\n";
        });
    }
}
