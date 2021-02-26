<?php


namespace App\HttpController;

use App\Models\Slide;
use App\Models\Notice;
use App\Models\NoticeGroup;

use App\Models\Task;

use App\Models\Game;
use App\Models\GameRecord;

use App\Models\Config;

class Index extends Base
{
    /**
     * @api {GET} /api/v1/index/list index
     *
     * @apiVersion 1.0.0
     * @apiName list
     *
     * @apiDescription Banner|公告|我的余额|累计收入|热门任务|最新中奖
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "silde": "轮播图banner",
     *       "notice": "公告_跑马灯",

     *       "activivty": "首页弹窗Activivty",
     *       "announcement": "首页弹窗Announcement",
     *
     *       "my": "我的余额",
     *       "myTotal" : "累计收入",
     *
     *       "hot": "热门任务_1个",
     *       "hot_2": "现金任务",
     *
     *       "lottery": "最新中奖",
     *     }
     * @apiGroup INDEX
     */
    public function list()
    {
        $data = [];
        try {
            //轮播图与首页活动图介绍
            $data['silde'] = $this->silde(NoticeGroup::NOTICE_GROUP_SLIDE);
            $data['activity'] = $this->silde(NoticeGroup::NOTICE_GROUP_ACTIVITY);

            //跑马灯 & 首页公告处理
            $data['notice'] = $this->notice(NoticeGroup::NOTICE_GROUP_MARQUEE);
            $data['announcement'] = $this->notice(NoticeGroup::NOTICE_GROUP_INDEX);

            //个人信息
            $data['my'] = $this->player();
            $data['myTotal'] = $this->playerInfo();

            //热点数据
            $data['hot'] = $this->taskHot();
            $data['hot_2'] = $this->invite();

            //游戏数据...
            $data['lottery'] = $this->GameRecord();

            return $this->responseWirteJsonSuccess($data);
        } catch (\Throwable $th) {
            return $this->responseWirteJsonError($th->getMessage());
        }
    }

    //轮播图 & activity  多个一个栏目用于
    public function silde($groupid = NoticeGroup::NOTICE_GROUP_SLIDE)
    {
        return Slide::create()->where([
            'status' => Slide::SLIDE_STATUS_DEFAULT,
            'lang_id'   => self::LANG_ENG,
            'groupid'   => $groupid,
            ])
        ->field(['img_path','content'])
        ->all() ?? [];
    }

    //公告-> 跑马灯(3)  & 首页公告(4)
    public function notice($groupid = NoticeGroup::NOTICE_GROUP_MARQUEE)
    {
        return Notice::create()->where([
            'groupid'    => $groupid,
            'state' => Notice::NOTICE_STATE_DEFAULT,
            'lang_id'   => self::LANG_ENG,
            ])
        ->field(['title','content','cover_img','url','created_at'])
        ->order('id', 'desc')
        ->all() ?? [];
    }

    //用户信息
    public function player()
    {
        $player = $this->getPlayer();
        return [
            'money' => (int) $player['money'],
            'coin'  => (int) $player['coin'],
            'invite_code' => $player['invite_code'],
        ];
    }
    //用户详细信息
    public function playerInfo()
    {
        $playerInfo = $this->getPlayerInfo();
        return [
            'total_money'   => (int)$playerInfo['total_money'],
            'total_coin'   => (int)$playerInfo['total_coin'],
        ];
    }
    //热门任务->只取一条. 并不能关闭的任务
    public function taskHot()
    {
        return  Task::create()->where([
            'status'    => self::PLACEHOLDER,
            'task.lang_id'   => self::LANG_ENG,
            'hot'   => self::NORMAL
        ])
        ->join('task_group', 'task_group.id = task.t_id')
        ->field(['task.id','t_id','task.title','task.info','total_price'])
        ->where(['task_group.state' => self::PLACEHOLDER])
        ->order('task.id', 'desc')
        ->get() ?? [];
    }


    public function GameRecord()
    {
        //最新中奖用户
        $gameRecord  = GameRecord::create()->where([
            'game_id'   => Game::GMAE_TURNTABLE,
        ])
        ->field(['player_id','prize','add_time'])
        ->order('id', 'desc')
        ->limit(self::DEFAULT_LIMIT)
        ->all();
        $gameArr = [];
        foreach ($gameRecord as $k => $v) {
            $gameArr[$k]['phone'] = $this->func_substr_replace($v->prize['phone']);
            $gameArr[$k]['coin'] = $v->prize['value'];
            $gameArr[$k]['add_time'] = $v->add_time;
        }
        return $gameArr;
    }


    public function invite()
    {
        return  Config::create()
            ->where([
                'id'    => Config::CONFIG_INDEX_COIN,
            ])
            ->field(['value'])
                ->get();
    }
}
