<?php
namespace App\HttpController;

use EasySwoole\ORM\DbManager;

use App\Models\Task as TaskModels;
use App\Models\TaskGroup;
use App\Models\TaskUser;
use App\Models\GameRecord;
use App\Models\Player;
use App\Models\Config;
use App\Models\Reward;
use App\Models\Consume;
use App\Models\DailyBonus;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\Validate\Validate;

//TODO: id物理主键 uuid 逻辑主键. 防止ID业务泄露处理
class Task extends Base
{
    /**
     * @api {GET} /api/v1/task/list list
     *
     * @apiVersion 1.0.0
     * @apiName list
     *
     * @apiDescription 任务列表-> 从任务分类获取对应的task_id传入对应分类
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *      "code" : 200,
     *      "result" :{
     *      "page" : "1",
     *      "page_size" : "10":
     *      "totalNum" : "总条数",
     *      "results" : {
     *       "id": "自增ID,用于传入领取任务",
     *       "title": "标题",
     *       "info": "简介",
     *       "content": "内容",
     *       "total_price": "任务总价",
     *       "total_number": "任务数量",
     *       "receive_number": "已领取的任务数量",
     *       "link_info": "链接信息",
     *       "link_info": "截止日期",
     *       "finish_condition": "完成条件",
     *       "created_at": "创建时间",
     *       "task_user_satus" : 0未领取  1：进行中；2：审核中；3：已完成；4：已失败;5:恶意',
     *       "taskGroup" : {
     *              当前任务组的栏目图标与图片,获取不涉及状态
     *              }
     *          }
     *      },
     *      "msg" : success,
     *     }
     *
     * @apiParam {Number} task_id  REMARK -> 任务分类ID   RULE -> required
     * @apiParam {Number} [page]   REMARK -> 页码 Default -> 1   RULE -> options
     * @apiParam {Number} [page_size]   REMARK -> 页码当前条数 Default -> 10   RULE -> options
     * @apiGroup TASK
     */
    public function list()
    {
        $where = [];
        $rate = self::PLACEHOLDER; //系统默认比率,出错的情况下选择
        try {
            $player = $this->getPlayer();

            $valitor = new Validate();
            $valitor->addColumn('task_id')->optional();
            $valitor->addColumn('timeIntervalBegin')->optional();
            $valitor->addColumn('timeIntervalEnd')->optional();
            $valitor->addColumn('page')->optional();
            $valitor->addColumn('page_size')->optional()->max(200);

            if ($valitor->validate($this->params) != true) {
                return $this->responseWirteJsonError($valitor->getError()->__toString());
            }
            $where['status'] = TaskModels::TASK_STATUS_DEFAULT;
            if (!empty($this->params['task_id'])) {
                $where['t_id'] = $this->params['task_id'];
            }
            //获取当前登录推广比率配置->如无则取系统配置.
            $config = Config::create()->where([
                'id'    => Config::CONFIG_EXTENSION_SETTINGS,
            ])->get();
            $configData = json_decode($config['data'], true);
            $selfRate = $this->jwtData['self_rate'] ?? $player['self_rate'];
            if (empty($selfRate)) {
                $rate = (int)$configData['self'] ;
            } else {
                $rate = (int)$selfRate ;
            }
            //relations
            $where['task_group.state'] = self::PLACEHOLDER;
            //任务是否已关闭,关闭当前分组都需关闭
            $taskResult =  TaskModels::create()->where($where)
                ->join('task_group', 'task_group.id = task.t_id')
                ->limit($this->params['page_size'] * ($this->params['page'] - 1), $this->params['page_size'])
                ->field(TaskModels::TASK_FIELD)
                ->all();
            
            $taskCount = TaskModels::create()
                    ->where($where)
                    ->join('task_group', 'task_group.id = task.t_id')
                    ->field(TaskModels::TASK_FIELD)
                    ->count('task.id');

            foreach ($taskResult as $k => $v) {
                $taskResult[$k]['total_price'] = (int)($v->total_price  * ($rate / self::RATE));
                // $v->taskGroup;
                //用户是否有领取,用relations是比较好. 当前ORM较弱
                $taskUserRelations =  TaskUser::create()->where([
                    'task_id'    => $v->id,
                    'player_id' => $this->jwtData['id'],
                ])->get();
                //图片地址转换
                $taskResult[$k]['relation_icon'] = DOMAIN_ADMIN.$v->relation_icon;
                if (!empty($taskUserRelations)) {
                    $taskResult[$k]['task_user_satus'] = $taskUserRelations['status'];
                } else {
                    $taskResult[$k]['task_user_satus'] = self::PLACEHOLDER;
                }
            }


            $data = [
                'page' => $this->params['page'],
                'page_size' => $this->params['page_size'],
                'totalNum' => $taskCount ?? self::PLACEHOLDER,
                'results'   => $taskResult,
            ];


            return $this->responseWirteJsonSuccess($data);
        } catch (\Throwable $th) {
            return $this->responseWirteJsonError($th->getMessage());
        }
    }
    /**
     * @api {GET} /api/v1/task/category category
     *
     * @apiVersion 1.0.0
     *
     * @apiName category
     *
     * @apiDescription 任务分类,图标显示.
     *
     * @apiGroup TASK
     */
    public function category()
    {
        $where = [];
        try {
            $TaskGroup = new TaskGroup();
            $where['state'] = TaskGroup::TASK_GROUP_STATE_DEFAULT;

            $result =  $TaskGroup->where($where)->order('sort', 'ASC')->all();
            return $this->responseWirteJsonSuccess($result);
        } catch (\Throwable $th) {
            return $this->responseWirteJsonError($th->getMessage());
        }
    }


    /**
     * @api {GET} /api/v1/task/user task_user
     *
     * @apiVersion 1.0.0
     *
     * @apiName task_user
     *
     * @apiDescription 当前登录用户,已领取的任务列表.
     *
     * @apiParam {Number} [page]   REMARK -> 页码 Default -> 1   RULE -> options
     * @apiParam {Number} [page_size]   REMARK -> 页码当前条数 Default -> 10   RULE -> options
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "1", //任务的状态。1：进行中；2：审核中；3：已完成；4：已失败;5:恶意',
     *       "lastname": "Doe"
     *     }
     * @apiGroup TASK
     */
    public function user()
    {
        try {
            $valitor = new Validate();
            $valitor->addColumn('page')->optional();
            $valitor->addColumn('page_size')->optional();
            if ($valitor->validate($this->params) != true) {
                return $this->responseWirteJsonError($valitor->getError()->__toString());
            }

            $taskModel = TaskUser::create()
                ->limit($this->params['page_size'] * ($this->params['page'] - 1), $this->params['page_size'])
                ->withTotalCount();
            $result = $taskModel->where([
                'player_id' => $this->jwtData['id'],
            ])
            ->field(TaskUser::TASK_USER_VISIBLE)
            ->all();
            $result2 = $taskModel->lastQueryResult();
            $total = $result2->getTotalCount();
            
            foreach ($result as $k => $v) {
                $v->task->title;
                if ($v->task->total_price) {
                    $v->task->total_price =  (int)($v->task->total_price  * ($this->rateAuto() / self::RATE));
                }
                if (isset($v->task->t_id)) {
                    $taskGroup = TaskGroup::create()->where([
                        'id'    => $v->task->t_id,
                    ])->get();
                    $result[$k]['taskGroup'] = $taskGroup;
                }
            }
            $data = [
                'page' => $this->params['page'],
                'page_size' => $this->params['page_size'],
                'totalNum' => $total ?? self::PLACEHOLDER,
                'results'   => $result,
            ];
            return $this->responseWirteJsonSuccess($data);
        } catch (\Throwable $th) {
            return $this->responseWirteJsonError($th->getMessage());
        }
    }





    /**
     * @api {POST} /api/v1/task/get task_get
     *
     * @apiVersion 1.0.0
     * @apiName task_get
     * @apiDescription 领取任务列表ID传入,同个任务只能领取一次.不能重复领取
     *
     * @apiParam {Number} task_id  REMARK -> 任务列表ID   RULE -> required
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "task_id": "任务ID",
     *       "status" =>  任务的状态。1：进行中；2：审核中；3：已完成；4：已失败;5:恶意'
     *     }
     * @apiGroup TASK
     */
    public function get()
    {
        try {
            $valitor = new Validate();
            $valitor->addColumn('task_id')->required('task_id is required!');
            if ($valitor->validate($this->params) != true) {
                return $this->responseWirteJsonError($valitor->getError()->__toString());
            }

            //1. 任务是否关闭 (非法提交表单表记录,用于筛选调试用户)
            $TaskModels = new TaskModels();
            $taskResult =  $TaskModels->where([
                'id'    => $this->params['task_id'],
                'status'    => TaskModels::TASK_STATUS_DEFAULT,
            ])->get();
            if (!$taskResult) {
                return $this->responseWirteJsonError(self::API_ILLEGAL_SUBMISSION);
            }
            //1.1 是否已达到最大的任务领取数量
            $taskUserCount = TaskUser::create()->where([
                'task_id'   => $this->params['task_id'],
            ])->count() ?? self::PLACEHOLDER;
            if ($taskUserCount >= $taskResult['total_number']) {
                return $this->responseWirteJsonError('The number of claims for the current task has been exceeded');
            }
            //1.2 是否已达到截止日期,如未设置则不限制
            if (!empty($taskResult['end_time'])) {
                if ($taskResult['end_time'] < time()) {
                    return $this->responseWirteJsonError('The current task has reached the deadline and cannot be claimed');
                }
            }


            //2. 任务是否重复领取判断
            $data = [
                'task_id'   => $this->params['task_id'],
                'player_id' => $this->jwtData['id'],
            ];
            $TaskUser = new TaskUser();
            $isGetTask =  $TaskUser->where($data)->get();
            if ($isGetTask) {
                return $this->responseWirteJsonError('Repeat task collection');
            }
            $data['add_time'] = time();
            $data['username'] = $this->jwtData['nickname'] == 'default' ? $this->jwtData['phone'] : 'default';
            $data['created_at'] = date('Y-m-d H:i:s', time());
            
            $TaskUser = new TaskUser();
            $result = $TaskUser->data($data)->save();
            if ($result) {
                //更新已领任务数量
                TaskModels::create()->update([
                    'receive_number'    => QueryBuilder::inc(1),
                ], [
                    'id'   => $this->params['task_id']
                ]);
                return $this->responseWirteJsonSuccess(['task_id' => $this->params['task_id'],'status' => self::NORMAL]);
            }
            throw new \Exception("Error Processing Request", 1);
        } catch (\Throwable $th) {
            return $this->responseWirteJsonError($th->getMessage());
        }
    }


    /**
     * @api {POST} /api/v1/task/submitTask submit_task
     *
     * @apiVersion 1.0.0
     * @apiName submit_task
     * @apiDescription 任务提交
     * @apiParam {number} task_id  REMARK -> task_id 任务ID  RULE -> required|numeric
     * @apiParam {string} img  REMARK -> img 图片链接  RULE -> required|lengthMax(200)
     * @apiParam {string} content  REMARK -> content 提交内容  RULE -> required|lengthMax(500)
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "task_id": "任务ID",
     *       "status" =>  任务的状态。1：进行中；2：审核中；3：已完成；4：已失败;5:恶意'
     *     }
     * @apiGroup TASK
     */
    public function submitTask()
    {
        try {
            $valitor = new Validate();
            $valitor->addColumn('task_id')->required('task_id is required!')->numeric();
            $valitor->addColumn('img')->required('img is required!')->lengthMax(200);
            $valitor->addColumn('content')->required('content is required!')->lengthMax(500);
            if ($valitor->validate($this->params) != true) {
                return $this->responseWirteJsonError($valitor->getError()->__toString());
            }
            //上传任务说明
            $data = [
                'task_id'   => $this->params['task_id'],
                'player_id' => $this->jwtData['id'],
            ];
            $TaskUser = new TaskUser();
            $isGetTask =  $TaskUser->where($data)->get();
            if ($isGetTask['status'] == TaskUser::TASK_STATUS_NORMAL) {
                $updateTask =  [
                    'submit_task'   => $this->params['content'],
                    'submit_img'   => $this->params['img'],
                    'status'    => TaskUser::TASK_STATUS_UNDER_REVIEW,
                ];
                TaskUser::create()->update($updateTask, [
                    'id'    => $isGetTask['id'],
                ]);
                return $this->responseWirteJsonSuccess(['task_id' =>$isGetTask['task_id'],'status' => TaskUser::TASK_STATUS_UNDER_REVIEW]);
            }
            return $this->responseWirteJsonSuccess('The task has been submitted, please wait for review');
        } catch (\Throwable $th) {
            return $this->responseWirteJsonError($th->getMessage());
        }
    }




    /**
     * @api {POST} /api/v1/task/submitTaskImg submit_task_img
     *
     * @apiVersion 1.0.0
     * @apiName submit_task_img
     * @apiDescription 上传任务图片说明
     * @apiParam {File} img  REMARK -> File   RULE -> allowFile ['jpg','gif','jpeg','png'] |  FileSize 2m
     *
     * @apiGroup TASK
     */
    public function submitTaskImg()
    {
        //TODO: 默认上传是单台服务器,不利于集群服务. 应当使用OSS服务
        //TODO: 上传过于频繁等操作 记录,防止服务器图片过多. 占用空间
        $allowFile = ['jpg','gif','jpeg','png'];
        $avatar =$this->request()->getUploadedFile('img');//获取一个上传文件,返回的是一个\EasySwoole\Http\Message\UploadFile的对象
        if ($avatar->getError() == self::PLACEHOLDER) {
            try {
                //文件-类型判断
                // $fileType = $avatar->getClientMediaType();
                //文件-大小
                $fileSize = $avatar->getSize();
                //文件-名称
                $fileName = $avatar->getClientFilename();
                $fileName = explode('.', $fileName);
                if (!in_array($fileName[1], $allowFile)) {
                    throw new \Exception("fileType no allow!", 1);
                }
                $savePath = APP_UPLOADS_PATH.'task/'.date('Ymd').'/';
                if (!file_exists($savePath)) {
                    mkdir($savePath, 777, true);
                }
                $randFileName =  uniqid().mt_rand(0, 9999).'.'.$fileName[1];
                //文件-移动
                $avatar->moveTo($savePath.$randFileName);
                $responsePath  =  APP_HOST.'task/'.date('Ymd').'/'.$randFileName;

                return $this->responseWirteJsonSuccess(['task' => $responsePath]);
            } catch (\Throwable $th) {
                return $this->responseWirteJsonError($th->getMessage());
            }
        } else {
            return $this->responseWirteJsonError($avatar->getError());
        }
    }




    /**
     * @api {POST} /api/v1/task/submitGiveUp submit_give_up
     *
     * @apiVersion 1.0.0
     * @apiName submit_give_up
     * @apiDescription 任务放弃
     * @apiParam {number} task_id  REMARK -> task_id 任务ID  RULE -> required|numeric
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "task_id": "任务ID",
     *       "status" =>  任务的状态。1：进行中；2：审核中；3：已完成；4：已失败;5:恶意'
     *     }
     * @apiGroup TASK
     */
    public function submitGiveUp()
    {
        try {
            $valitor = new Validate();
            $valitor->addColumn('task_id')->required('task_id is required!');
            if ($valitor->validate($this->params) != true) {
                return $this->responseWirteJsonError($valitor->getError()->__toString());
            }
            //放弃当前任务
            $data = [
                'task_id'   => $this->params['task_id'],
                'player_id' => $this->jwtData['id'],
            ];
            $TaskUser = new TaskUser();
            $isGetTask =  $TaskUser->where($data)->get();
            if ($isGetTask['status'] == TaskUser::TASK_STATUS_NORMAL) {
                $updateTask =  [
                    'status'   => TaskUser::TASK_STATUS_GIVE_UP,
                ];
                TaskUser::create()->update($updateTask, [
                    'id'    => $isGetTask['id'],
                ]);
                return $this->responseWirteJsonSuccess(['task_id' =>$isGetTask['task_id'],'status' => TaskUser::TASK_STATUS_GIVE_UP ]);
            }
            return $this->responseWirteJsonSuccess('The task has been submitted, please wait for review');
        } catch (\Throwable $th) {
            return $this->responseWirteJsonError($th->getMessage());
        }
    }


    /**
     * @api {GET} /api/v1/task/cashHall cash_hall
     *
     * @apiVersion 1.0.0
     * @apiName cash_hall
     *  @apiDescription 现金大厅任务列表
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "invite_config": "二维码邀请 赠送配置 key(任务) value(人数达到) desc (得现金数 送",
     *       "daily_bonus_config": "每日签到配置 key(签到天数) value(当天邀请新朋友个数)  desc(获得现金RS)",
     *       "invite_sub_num": "当前已邀请的人数",
     *       "invite_receive_status": "是否能领取任务状态,能领取则请求cashInvite",
     *       "report_condition_first": "每日签到所需完成条件1",
     *       "report_condition_second": "每日签到所需完成条件2",
     *       "report_value": "当前档位所需邀请人数",
     *       "report_desc": "当前档位所赠送的现金",
     *       "report_status": "first_secoond为1情况下,可以请求cashDailyBonus接口 获取现金",
     *       "report_data": "是否存在签到数据,定时凌晨处理自增或者delete新的周期",
     *     }
     *  @apiGroup TASK
     */
    public function cashHall()
    {
        //Task Hall 现金任务 手动放入列表 与后台配置  report & invite
        try {
            //daily bonus task  1.paly games(大转盘) 2. invite 1 new friends (邀请一个新朋友)
            $palyGamesCount = GameRecord::create()->where([
                'player_id' => $this->jwtData['id'],
                'add_time'  => $this->todaySuffix,
            ])->count() ?? self::PLACEHOLDER;
            $inviteFriendsCount = Player::create()
                ->where('parent_id', $this->jwtData['id'])
                ->where('register_time', [$this->todaySuffix,$this->tomorrowSuffix], 'between')
                ->count() ?? self::PLACEHOLDER;
            $invite =  Config::create()->where([
                'id'    => Config::CONFIG_MONEY_INVITE_TOTAL,
            ])
            ->field(['desc2'])
            ->get();
            $dailyBonus =  Config::create()->where([
                'id'    => Config::CONFIG_DAILY_BONUS,
            ])
            ->field(['desc2','data'])
            ->get();
            $dailyBonusLimit = json_decode($dailyBonus['data'], true);
            $player = $this->getPlayer();
            
            //INVITE order ASC
            $inviteReward = Reward::create()->where([
                'player_id'    => $this->jwtData['id'],
                'type'  => Reward::REWARD_TYPE_INVITE_CASH,
                'status'    => self::PLACEHOLDER,
            ])->get();
            
            //report -> daily bonus data
            $playerDailyBonus =  DailyBonus::create()->where([
                'player_id' => $this->jwtData['id']
            ])
            ->field(['frequency','status'])
            ->get();

            $dailyBonusDesc =json_decode($dailyBonus['desc2'], true);
            $reportValue = 0; //第一天;
            $reportDesc = 0;
            //玩家当前所在的档次
            if (!empty($playerDailyBonus)) {
                $reportValue = $dailyBonusDesc[$playerDailyBonus['frequency'] - self::NORMAL]['value'];
                $reportDesc = $dailyBonusDesc[$playerDailyBonus['frequency'] - self::NORMAL]['desc'];
            } else {
                $reportValue = $dailyBonusDesc[self::PLACEHOLDER]['value'];
                $reportDesc = $dailyBonusDesc[self::PLACEHOLDER]['desc'];
            }

            $data = [
                'invite_config'    => json_decode($invite['desc2'], true),
                'daily_bonus_config' => $dailyBonusDesc,
                'invite_sub_num'    => $player['sub_num'] ?? self::PLACEHOLDER,
                'invite_receive_status'    => !empty($inviteReward) ? self::NORMAL : self::PLACEHOLDER,

                //dailybonus
                'report_condition_first'    => $palyGamesCount >= $dailyBonusLimit['palyGames'] ?  self::NORMAL : self::PLACEHOLDER,
                'report_condition_second'   => $inviteFriendsCount >= $dailyBonusLimit['inviteNewFriends'] ?  self::NORMAL : self::PLACEHOLDER,
                //当前天数不存在取1,否则取
                'report_value'  => $reportValue ?? self::PLACEHOLDER,
                'report_desc'   => $reportDesc ?? self::PLACEHOLDER,

                'report_status' => $playerDailyBonus['status'] ?? self::PLACEHOLDER, //是否已领状态
                'report_data'   => $playerDailyBonus ?? [], //是否存在签到数据
            ];
            return $this->responseWirteJsonSuccess($data);
        } catch (\Throwable $th) {
            return $this->responseWirteJsonError($th->getMessage());
        }
    }


    /**
     * @api {POST} /api/v1/task/cashInvite cash_invite
     *
     * @apiVersion 1.0.0
     * @apiName cash_invite
     * @apiDescription invite_receive_status为1时提交任务处理,自动领取最近的一条达标邀请任务
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "invite_receive_status" =>  1还可以继续领取 0为已不能领取
     *     }
     *
     * @apiGroup TASK
     */
    public function cashInvite()
    {
        try {
            DbManager::getInstance()->startTransaction();
            //满足任务领取操作
            $reward = Reward::create()
                ->where([
                    'player_id'    => $this->jwtData['id'],
                    'type'  => Reward::REWARD_TYPE_INVITE_CASH,
                    'status'    => self::PLACEHOLDER,
                ])
                ->get();
            if (empty($reward)) {
                throw new \Exception("Error Processing Request", 1);
            }
            $player = $this->getPlayer();

            if ($this->playerMoneyUpdateUntiy(
                (int)$this->jwtData['id'],
                (int)$reward['reward'],
                [],
                'inc',
                'money'
            )) {
                //更新完成状态处理
                Reward::create()->update([
                    'status'    => self::NORMAL,
                ], [
                    'id'    => $reward['id']
                ]);
                $this->playerRecordUntiyAdd(
                    (int)$this->jwtData['id'],
                    (int)$player['money'],
                    (int)$reward['reward'],
                    Consume::CONSUME_CURRENCY_CASH,
                    Consume::CONSUME_TYPE_INVITE_GIVE
                );
                //检测是否还存在未领取的任务
                $rewardNext = Reward::create()
                ->where([
                    'player_id'    => $this->jwtData['id'],
                    'type'  => Reward::REWARD_TYPE_INVITE_CASH,
                    'status'    => self::PLACEHOLDER,
                ])
                ->get();
                if ($rewardNext) {
                    return $this->responseWirteJsonSuccess(['invite_receive_status' => self::NORMAL]);
                }
                return $this->responseWirteJsonSuccess(['invite_receive_status' => self::PLACEHOLDER]);
            }
            throw new \Exception("Error Processing Request", 1);
        } catch (\Throwable $th) {
            DbManager::getInstance()->rollback();
            return $this->responseWirteJsonError($th->getMessage());
        } finally {
            DbManager::getInstance()->commit();
        }
    }

    /**
     * @api {POST} /api/v1/task/cashDailyBonus cash_daily_bonus
     *
     * @apiVersion 1.0.0
     * @apiName cash_daily_bonus
     * @apiDescription 每日签到任务,完成任务达标时 提交任务. 凌晨定时脚本处理签到任务
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       result = >{
     *          string ->  The daily check-in task has been completed that day 当天的每日值机任务已完成
     *              The daily check-in task has been completed on the day, please submit repeatedly 每日值机任务已于当天完成，请重复提交
     *       }
     *       "report_status" =>  等待凌晨刷新 0只是单独返回 应刷新列表取first-second值判断提交是否可以提交
     *     }
     * @apiGroup TASK
     */
    public function cashDailyBonus()
    {
        try {
            $palyGamesCount = GameRecord::create()->where([
                'player_id' => $this->jwtData['id'],
                'add_time'  => $this->todaySuffix,
            ])->count() ?? self::PLACEHOLDER;
            $inviteFriendsCount = Player::create()
                ->where('parent_id', $this->jwtData['id'])
                ->where('register_time', [$this->todaySuffix,$this->tomorrowSuffix], 'between')
                ->count() ?? self::PLACEHOLDER;
    
            $dailyBonus =  Config::create()->where([
                'id'    => Config::CONFIG_DAILY_BONUS,
            ])
            ->field(['desc2','data'])
            ->get();
            $dailyBonusLimit = json_decode($dailyBonus['data'], true);
            $dailyBonusDesc = json_decode($dailyBonus['desc2'], true);
            //分成两个限制条件,外部的玩游戏次数 与限制全局的邀请人数
            if ((int)$palyGamesCount >=  (int)$dailyBonusLimit['palyGames'] &&
                (int)$inviteFriendsCount >= (int)$dailyBonusLimit['inviteNewFriends']) {
                //id,player_id,frequency(签到次数),add_time,status(0,1 0的情况晚上delete 1次数+1 重置状态0),created_at,updated_at
                $daily =  DailyBonus::create()->where([
                    'player_id' => $this->jwtData['id']
                ])->get();
                $player = $this->getPlayer();
                //1.key 天数 value 邀请朋友配置  desc 表示可赠送的金额  2. 发放奖励
                if (empty($daily)) {
                    foreach ($dailyBonusDesc as $k => $v) {
                        if ($v['key'] == self::NORMAL && $inviteFriendsCount >= $v['value']) {
                            $playerUpdate =  $this->playerMoneyUpdateUntiy(
                                (int) $this->jwtData['id'],
                                (int) $v['desc'],
                                [],
                                'inc',
                                'money'
                            );
                            if ($playerUpdate) {
                                $this->playerRecordUntiyAdd(
                                    (int) $this->jwtData['id'],
                                    (int) $player['money'],
                                    (int) $v['desc'],
                                    Consume::CONSUME_CURRENCY_CASH,
                                    Consume::CONSUME_TYPE_DAILY_BONUS,
                                    $v
                                );
                                $data = [
                                    'player_id' => $this->jwtData['id'],
                                    'frequency' => self::NORMAL,
                                    'add_time'  => time(),
                                    'status'    => self::NORMAL,
                                    'created_at'    => date('Y-m-d H:i:s', time()),
                                ];
                                DailyBonus::create()->data($data)->save();
                            }
                        }
                    }
                    return $this->responseWirteJsonSuccess('The daily check-in task has been completed that day');
                } else {
                    if ($daily['status'] == self::NORMAL) {
                        return  $this->responseWirteJsonSuccess('The daily check-in task has been completed on the day, please submit repeatedly');
                    }
                    //判断是否满足条件处理
                    foreach ($dailyBonusDesc as $k => $v) {
                        if ($v['key'] == $daily['frequency']
                        && $inviteFriendsCount >= $v['value']
                        && $daily['status'] != self::NORMAL) {
                            $playerUpdate =  $this->playerMoneyUpdateUntiy(
                                (int) $this->jwtData['id'],
                                (int) $v['desc'],
                                [],
                                'inc',
                                'money'
                            );
                            if ($playerUpdate) {
                                $this->playerRecordUntiyAdd(
                                    (int) $this->jwtData['id'],
                                    (int) $player['money'],
                                    (int) $v['desc'],
                                    Consume::CONSUME_CURRENCY_CASH,
                                    Consume::CONSUME_TYPE_DAILY_BONUS,
                                    $v
                                );
                                DailyBonus::create()->update([
                                    'frequency' => QueryBuilder::inc(self::NORMAL),
                                    'status'    => self::NORMAL,
                                    'updated_at' => date('Y-m-d H:i:s', time()),
                                ], [
                                    'player_id'    =>  $this->jwtData['id'],
                                ]);
                            }
                        }
                    }
                    return $this->responseWirteJsonSuccess(['report_status' => self::PLACEHOLDER]);
                }
            }
            throw new \Exception("Error Processing Request", 1);
        } catch (\Throwable $th) {
            return $this->responseWirteJsonError($th->getMessage());
        }
    }
}
