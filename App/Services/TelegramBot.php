<?php
namespace App\Services;

use Telegram\Bot\Api;

/**
 *
 * 机器人通知->提现或者审核任务时notify  127.0.0.1:1080-->docker-->v2ray(TLS) class
 */
class TelegramBot
{
    protected $tg;
    //india_task_bot(TEST) -> https://api.telegram.org/bot1506380316:AAGKP5nKgS1jzhBM0k3sMWZJ1vEZKIJwcNM/getUpdates
    const INDIA_TASK_BOT_TOKEN = 'xxx';
    //BIND websocket https://api.telegram.org/bot（token）/setwebhook?url=callback url
    const CALLBACK_URL = 'xxx';

    public function __construct()
    {
        $this->tg = new Api(self::INDIA_TASK_BOT_TOKEN);
    }

    //本地需要代理才能使用,或者直接远程服务器调试 debug节点
    public function testTg()
    {
        try {
            $response = $this->tg->getMe();

            $botId = $response->getId();
            $firstName = $response->getFirstName();
            $username = $response->getUsername();
            var_dump($botId, $firstName, $username);
        } catch (\Throwable $th) {
            var_dump($th->getMessage());
        }
    }
    
    public function notifyPay()
    {
    }
}
