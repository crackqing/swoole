<?php


namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;

use App\Models\Player;
use EasySwoole\Jwt\Jwt;

//LIB
use App\Lib\Lottery;
use App\Lib\Tool;
use App\Lib\func;


use App\Task\DetectTask; 
//图片地址使用
define('APP_UPLOADS_PATH', EASYSWOOLE_ROOT.'/public/');
abstract class Base extends Controller
{
    use Lottery,Tool,func;

    //DEFAULT
    const NORMAL = 1;
    const PLACEHOLDER = 0;
    const SECOND = 2;
    const THREE = 3;
    const DEFAULT_LIMIT = 10;
    const DEFAULT_QUERY_LIMIT = 50;
    const DEFAULT_SELF_RATE = 10;

    const RATE = 100; //计算用

    
    const QUERY_BUILDER_DEC ='dec';


    //API
    const API_STATUS_SUCCESS_CODE = 200;
    const API_STATUS_ERROR_CODE = 1003;

    const API_LOGIN_EXPIRATION = 1000; //登录态过期 错误码集合处理
    const API_SMS_SENDING_INTERVAL = 1001; //手机短信发送间隔处理
    const API_LOGIN_EXPIRATION_STRING = 'The login status of the account has expired. Please log in again.';
    const API_ILLEGAL_SUBMISSION = 'ILLEGAL_SUBMISSION! Has been identified';


    const API_SUCCESS_MSG = 'success';
    const API_ERROR_MSG = 'error';
    const API_NO_PERMISSION = 'no_permission';




    //JWT SETTINGS
    const JWT_SECERT = 'TASK@#$TASK';
    const JWT_EXPIRATION = 86400 * 3; //86400 * 1  当时时间缀
    const JWT_PUBLISHER = 'fackbook';

    const JWT_VERIFICATION_PASSED = 1;
    const JWT_INVALID = -1;
    const JWT_TOKEN_EXPIRED = -2;


    //SALT
    const SALT_SHA1 ='DSGOP';

    //WEBSOCKET 用于推送指定玩家手机号码,来进行前端页面处理 或者后台的操作等等
    const WEB_SOCKET_TOKEN = 'bbaf038d0943de8319727c6ab8d024bfc3ef41634e504a3aba1f1e96653d8d48';
    const WEB_SOCKET_PHONE = 'xxxx';


    //TABLE->LANG
    const LANG_ENG = 1;
    const LANG_ZH = 2;
    const LANG_ZH_CN = 3;
    const LANG_INDIA = 4;

    protected $params;
    protected $serverParams;
    protected $headerParams;
    protected $swooleParams;

    protected $todaySuffix;
    protected $tomorrowSuffix;

    protected $startTimeMs = 0;
    protected $endTimeMs = 0;

    protected $jwtData = [];

    protected $skipCheck = [
        '/api/v1/user/login',
        '/api/v1/user/register',
        '/api/v1/external/bind',
        '/api/v1/external/getBind',
        '/api/v1/services/payNotify',
        '/api/v1/services/sms',
        '/api/v1/services/telegram'
    ];
    /**
     * 定义中间件 校验 function
     *  1. 请求中接口调用统计设计
     * @param string|null $action
     * @return boolean|null
     */
    protected function onRequest(?string $action): ?bool
    {
        $this->startTimeMs = microtime(true);

        $this->params = $this->request()->getRequestParam();
        $this->serverParams = $this->request()->getServerParams();
        $this->headerParams = $this->request()->getHeaders();
        $this->swooleParams = $this->request()->getSwooleRequest();


        $this->todaySuffix = strtotime('today');
        $this->tomorrowSuffix = strtotime('tomorrow');
        //0.统计接口请求,用于限流或非法拦截处理 & 安全 与 限流 (单IP每分钟请求不超过1000次)

        //1.检测所有接口,去除指定的接口
        if (!in_array($this->serverParams['request_uri'], $this->skipCheck)) {
            //1.1 验证TOKEN 与 过期时间. 所有请求都需带上token (herder)
            $token = isset($this->headerParams['token'][self::PLACEHOLDER]) ? $this->headerParams['token'][self::PLACEHOLDER]: '';
            if (empty($token)) {
                $this->writeJson(self::API_LOGIN_EXPIRATION, [self::API_LOGIN_EXPIRATION_STRING], self::API_ERROR_MSG);
                return false;
            }
            $jwtObject = Jwt::getInstance()->setSecretKey(self::JWT_SECERT)->decode($token);
            $status = $jwtObject->getStatus();
            switch ($status) {
                case  self::JWT_VERIFICATION_PASSED:
                    $this->jwtData = $jwtObject->getData();
                    break;
                case  self::JWT_INVALID:
                    $this->writeJson(self::API_LOGIN_EXPIRATION, [self::API_LOGIN_EXPIRATION_STRING], self::API_ERROR_MSG);
                    return false;
                    break;
                case  self::JWT_TOKEN_EXPIRED:
                    $this->writeJson(self::API_LOGIN_EXPIRATION, [self::API_LOGIN_EXPIRATION_STRING], self::API_ERROR_MSG);
                    return false;
                break;
            }
            $redis = \EasySwoole\RedisPool\RedisPool::defer('redis');
            $playerRedisHMGET = $redis->hMGet(Player::REDIS_PLAYER_HMSET.$this->jwtData['phone'], ['token','expiration','id']);
            if (empty($playerRedisHMGET)) {
                $this->writeJson(self::API_LOGIN_EXPIRATION, [self::API_LOGIN_EXPIRATION_STRING], self::API_ERROR_MSG);
                return false;
            }
            if ($playerRedisHMGET[0] != $token || $playerRedisHMGET[1] < time()) {
                $this->writeJson(self::API_LOGIN_EXPIRATION, [self::API_LOGIN_EXPIRATION_STRING], self::API_ERROR_MSG);
                return false;
            }
            //只允许单设备登录 uuid不相同情况下,清空登录态
            $this->jwtData['id'] = $playerRedisHMGET[2];
        }
        //INIT PAGE & 检测帐号状态
        if (!isset($this->params['page'])) {
            $this->params['page']  = self::NORMAL;
        }
        if (!isset($this->params['page_size'])) {
            $this->params['page_size'] = self::DEFAULT_LIMIT;
        }
        if (isset($this->params['page']) && $this->params['page_size']) {
            $this->params['page'] =  (int) $this->params['page'];
            $this->params['page_size'] = (int) $this->params['page_size'];
        }
        $task = \EasySwoole\EasySwoole\Task\TaskManager::getInstance();
        $task->async(new DetectTask(['phone' => $this->jwtData['phone']]));

        //2. 安全 与 限流 (单IP每分钟请求不超过1000次) TOKEN 写死不单独处理

        //3. 推送异步任务,记录请求接口的访问次数



        return true;
    }

    //重复登录的处理
    public function jwtStatus($token)
    {
        $jwtObject = Jwt::getInstance()->setSecretKey(self::JWT_SECERT)->decode($token);
        $status = $jwtObject->getStatus();
        switch ($status) {
                case  self::JWT_VERIFICATION_PASSED:
                    $this->jwtData = $jwtObject->getData();
                    break;
                case  self::JWT_INVALID:
                    $this->writeJson(self::API_LOGIN_EXPIRATION, [self::API_LOGIN_EXPIRATION_STRING], self::API_ERROR_MSG);
                    return false;
                    break;
                case  self::JWT_TOKEN_EXPIRED:
                    $this->writeJson(self::API_LOGIN_EXPIRATION, [self::API_LOGIN_EXPIRATION_STRING], self::API_ERROR_MSG);
                    return false;
                break;
            }
    }


    /**
     * 反代IP域名获取 与IP接口接口获取 function
     *
     * @return string
     */
    public function IP() : string
    {
        if (isset($this->swooleParams->header['x-real-ip']) ||
            isset($this->swooleParams->header['x-forwarded-for'])) {
            return $this->swooleParams->header['x-real-ip'] ?? $this->swooleParams->header['x-forwarded-for'];
        }
        return $this->serverParams['remote_addr'] ?? '127.0.0.1';
    }


    /**
     * 方法未实现 function
     *  1.可定义自定义异常== beforeAction & after
     * @param string|null $action
     * @return void
     */
    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    public function onException(\Throwable $throwable): void
    {
        // var_dump($throwable->getMessage());
    }

    /**
     * 订单号统一生成 function
     *  固定15位
     * @param [type] $playerId
     * @param [type] $region 印度 91
     * @return void
     */
    public function orderId($playerId = '', $region = 91) : string
    {
        $str = '';
        $str .= date('Ymdhis', time()).$playerId.$this->getMillisecond().$region;
        return $str;
    }

    /**
     * 统一封装 响应错误处理,用于后续修改与更新处理 function
     *
     * @param [type] $data
     * @return void
     */
    public function responseWirteJsonError($data = '') : void
    {
        $this->writeJson(self::API_STATUS_ERROR_CODE, $data, self::API_ERROR_MSG);
    }
    

    /**
     * 统一封装 响应错误处理,用于后续修改与更新处理 function
     *
     * @param [type] $data
     * @return void
     */
    public function writeJsonResErr($code = self::API_STATUS_ERROR_CODE, $data, $msg = self::API_ERROR_MSG) : void
    {
        $this->writeJson($code, $data, $msg);
    }

    /**
     * 统一封装 响应成功处理,用于后续修改与更新处理 function
     *
     * @param [type] $data
     * @return void
     */
    public function responseWirteJsonSuccess($data = '') : void
    {
        $this->writeJson(\EasySwoole\Http\Message\Status::CODE_OK, $data, self::API_SUCCESS_MSG);
    }


    public function responseIllegalSubmission() :void
    {

        //TASK--> 非法提现记录
        $this->writeJson(self::API_STATUS_ERROR_CODE, self::API_ILLEGAL_SUBMISSION, self::API_ERROR_MSG);
    }

    public function responseNoPermission(): void
    {
        //TASK--> 非法操作记录
        $this->writeJson(\EasySwoole\Http\Message\Status::CODE_FORBIDDEN, '', self::API_NO_PERMISSION);
    }
}
