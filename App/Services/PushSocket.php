<?php
namespace App\Services;

//推送websocket服务,玩家注册送金的等操作
class PushSocket
{

    /**
     * 全局推送使用 function
     *  1.也是easyswoole启动的一个websocket服务. 用来API与后台使用,前端推送数据
     *      1.1 测试时可找websocket客户端工具. 增加TOKEN与phone手机号调试就行
     * @param string $controller
     * @param string $action
     * @param array $params
     * @return void
     */
    public function push(string $controller, string $action, array $params)
    {
        $client = new \EasySwoole\HttpClient\HttpClient(APP_WEB_SOCKET_PUSH);
        $upgradeResult = $client->upgrade(true);
        $frame = new \Swoole\WebSocket\Frame();
        //设置发送的消息帧
        $frame->data = json_encode(['controller'=>$controller,'action'=>$action,'param'=>$params]);
        $pushResult = $client->push($frame);
        $recvFrame = $client->recv();
        //将返回bool或一个消息帧，可自行判断
        // var_dump($recvFrame->data);
    }
}
