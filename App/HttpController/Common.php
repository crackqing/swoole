<?php
namespace App\HttpController;

use App\Models\Config;

//公共配置文件
class Common extends Base
{
    /**
     * @api {GET} /api/v1/common/config config
     *
     * @apiVersion 1.0.0
     * @apiName config
     * @apiDescription 公共配置
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "withdraw": {"minimum_withdrawal":"100","highest_withdrawal":"10000"}, 余额提现限制
     *     }
     *
     *
     * @apiGroup CONFIG
     */
    public function config()
    {
        try {
            $withdraw = Config::create()->where([
                'id'    => Config::CONFIG_WITHDRAW_LIMIT,
            ])
            ->field(['data'])
            ->get();
            
            $give = Config::create()->where([
                'id'    => Config::CONFIG_REGISTER_GIVE_GOLD,
            ])->field(['data'])
            ->get();

            $data = [
                'withdraw'  => json_decode($withdraw['data'], true),
                'give'  => json_decode($give['data'], true),
    
            ];
            return $this->responseWirteJsonSuccess($data);
        } catch (\Throwable $th) {
            return $this->responseWirteJsonError($th->getMessage());
        }
    }
}
