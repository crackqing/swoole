<?php


namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use FastRoute\Route;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    public function initialize(RouteCollector $routeCollector)
    {
        $this->setGlobalMode(true);
        //TASK 任务系统 API接口与文档处理
        $routeCollector->addGroup('/api/v1/', function (RouteCollector $router) {
            //user register & login & logout
            $router->addRoute('POST', 'user/register', '/User/register');
            $router->addRoute('POST', 'user/login', '/User/login');
            $router->addRoute('POST', 'user/logout', '/User/logout');
            //user 具体操作 绑定 修改 下级 提现与金币记录
            $router->addRoute('POST', 'user/uploadAvatar', '/User/uploadAvatar');
            $router->addRoute('POST', 'user/personalDetails', '/User/personalDetails');
            $router->addRoute('POST', 'user/bankInformation', '/User/bankInformation');
            $router->addRoute('POST', 'user/bankEdit', '/User/bankEdit');
            $router->addRoute('POST', 'user/changePassword', '/User/changePassword');

            $router->addRoute('GET', 'user/refresh', '/User/refresh');
            $router->addRoute('GET', 'user/team', '/User/team');
            $router->addRoute('GET', 'user/teamSub', '/User/teamSub');
            
            $router->addRoute('GET', 'user/record', '/User/record');
            $router->addRoute('POST', 'user/withdraw', '/User/withdraw');
            $router->addRoute('GET', 'user/withdrawRecord', '/User/withdrawRecord');

            
            //task 金币
            $router->addRoute('GET', 'task/list', '/Task/list');
            $router->addRoute('GET', 'task/category', '/Task/category');
            $router->addRoute('GET', 'task/user', '/Task/user');
            $router->addRoute('POST', 'task/get', '/Task/get');
            $router->addRoute('POST', 'task/submitTaskImg', '/Task/submitTaskImg');
            $router->addRoute('POST', 'task/submitTask', '/Task/submitTask');
            $router->addRoute('POST', 'task/submitGiveUp', '/Task/submitGiveUp');
            //task 大厅任务领取 与 提交任务
            $router->addRoute('GET', 'task/cashHall', '/Task/cashHall');
            $router->addRoute('POST', 'task/cashInvite', '/Task/cashInvite');
            $router->addRoute('POST', 'task/cashDailyBonus', '/Task/cashDailyBonus');


            //index
            $router->addRoute('GET', 'index/list', '/Index/list');
            
            //GAME
            $router->addRoute('GET', 'game/config', '/Game/config');
            $router->addRoute('GET', 'game/turntable', '/Game/turntable');
            $router->addRoute('GET', 'game/refreshLottery', '/Game/refreshLottery');
            
            //公共-> 模块处理
            $router->addRoute('GET', 'common/config', '/Common/config');

            //TEST-> http or Command
            $router->addRoute('GET', 'test/list', '/Test/list');

            //EXTERNAL-> break JWT
            $router->addRoute('POST', 'external/bind', '/External/bind');
            $router->addRoute('POST', 'external/getBind', '/External/getBind');

            //SERVICES-> sms & pay (notify & process)
            $router->addRoute('POST', 'services/sms', '/Services/sms');
            $router->addRoute(['GET','POST'], 'services/payNotify', '/Services/payNotify');
            
            //fackbook & telegramBot  apicall

            $router->addRoute('GET','services/telegram','/Services/telegram');
        });





        //未找到处理方法
        $this->setMethodNotAllowCallBack(function (Request $request, Response $response) {
            return false;//结束此次响应
        });
        //未找到路由匹配
        $this->setRouterNotFoundCallBack(function (Request $request, Response $response) {
            return 'index';//重定向到index路由
        });




        //$collector->addRoute('GET', '/lottery/{lottery_name}/{limit:\d+}/{token}/{type}[/{start_time}[/{end_time}]]', '/Lottery/getLotteryData');
        // $routeCollector->get('/doc', '/Index/doc');
        /*
         * eg path : /closure/index.html  ; /closure/ ;  /closure
         */
        // $routeCollector->get('/closure', function (Request $request, Response $response) {
        //     $response->write('this is closure router task');
        //     //不再进入控制器解析
        //     return false;
        // });
    }
}
