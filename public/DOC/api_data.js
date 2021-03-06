define({ "api": [
  {
    "type": "GET",
    "url": "/api/v1/common/config",
    "title": "config",
    "version": "1.0.0",
    "name": "config",
    "description": "<p>公共配置</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"withdraw\": {\"minimum_withdrawal\":\"100\",\"highest_withdrawal\":\"10000\"}, 余额提现限制\n}",
          "type": "json"
        }
      ]
    },
    "group": "CONFIG",
    "filename": "App/HttpController/Common.php",
    "groupTitle": "CONFIG"
  },
  {
    "type": "POST",
    "url": "/api/v1/external/bind",
    "title": "bind",
    "version": "1.0.0",
    "name": "bind",
    "description": "<p>推送绑定数据</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "ip",
            "description": "<p>REMARK -&gt;          RULE -&gt; required</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "invite_code",
            "description": "<p>REMARK -&gt;          RULE -&gt; required</p>"
          }
        ]
      }
    },
    "group": "EXTERNAL",
    "filename": "App/HttpController/External.php",
    "groupTitle": "EXTERNAL"
  },
  {
    "type": "POST",
    "url": "/api/v1/external/getBind",
    "title": "get_bind",
    "version": "1.0.0",
    "name": "get_bind",
    "description": "<p>获取绑定数据</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "ip",
            "description": "<p>REMARK -&gt;     RULE -&gt; options</p>"
          }
        ]
      }
    },
    "group": "EXTERNAL",
    "filename": "App/HttpController/External.php",
    "groupTitle": "EXTERNAL"
  },
  {
    "type": "GET",
    "url": "/api/v1/game/config",
    "title": "game_config",
    "version": "1.0.0",
    "name": "game/config_游戏配置",
    "description": "<p>当前游戏配置信息,概率等重要信息不返回.</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "game_id",
            "description": "<p>REMARK -&gt; 游戏id 默认 1 =&gt; 大转盘   RULE -&gt; required</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": \"200\",\n  \"result\": {\n     \"name\" : \"游戏名称\"\n     \"consumption\" : \"1000\" 每次游玩需要花费的金币\n     \"content\" : {不同游戏数值不同,需沟通设置}\n  }\n  \"msg\": \"success\"\n}",
          "type": "json"
        }
      ]
    },
    "group": "GAME",
    "filename": "App/HttpController/Game.php",
    "groupTitle": "GAME"
  },
  {
    "type": "GET",
    "url": "/api/v1/game/turntable",
    "title": "game_turntabel",
    "version": "1.0.0",
    "name": "game_turntabel",
    "description": "<p>大转盘</p>",
    "group": "GAME",
    "filename": "App/HttpController/Game.php",
    "groupTitle": "GAME"
  },
  {
    "type": "GET",
    "url": "/api/v1/index/list",
    "title": "index",
    "version": "1.0.0",
    "name": "list",
    "description": "<p>Banner|公告|我的余额|累计收入|热门任务|最新中奖</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"silde\": \"轮播图banner\",\n  \"notice\": \"公告\",\n  \"my\": \"我的余额\",\n  \"myTotal\" : \"累计收入\",\n  \"hot\": \"热门任务\",\n  \"lottery\": \"最新中奖\",\n}",
          "type": "json"
        }
      ]
    },
    "group": "INDEX",
    "filename": "App/HttpController/Index.php",
    "groupTitle": "INDEX"
  },
  {
    "type": "POST",
    "url": "/api/v1/user/bankEdit",
    "title": "bankEdit",
    "version": "1.0.0",
    "name": "bankEdit",
    "description": "<p>银行卡信息编辑,只能修改一次</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "bank",
            "description": "<p>REMARK -&gt; bank   RULE -&gt; required|lengthMax(40)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "bankName",
            "description": "<p>REMARK -&gt; bankName   RULE -&gt; required|lengthMax(40)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "Number",
            "description": "<p>REMARK -&gt; Number   RULE -&gt; required|lengthMin(9)|lengthMax(40)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "Ifsc",
            "description": "<p>REMARK -&gt; Ifsc   RULE -&gt; required|lengthMin(10)|lengthMax(40)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "mobile",
            "description": "<p>REMARK -&gt; mobile   RULE -&gt; required|lengthMax(40)</p>"
          }
        ]
      }
    },
    "group": "PLAYER",
    "filename": "App/HttpController/User.php",
    "groupTitle": "PLAYER"
  },
  {
    "type": "GET",
    "url": "/api/v1/user/changePassword",
    "title": "change_password",
    "version": "1.0.0",
    "name": "change_password",
    "description": "<p>修改密码</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "old_password",
            "description": "<p>REMARK -&gt;  旧密码   RULE -&gt; required|betweenLen(6,20)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "password",
            "description": "<p>REMARK -&gt;  新密码   RULE -&gt; required|betweenLen(6,20)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "again_password",
            "description": "<p>REMARK -&gt;  新密码在输入一次   RULE -&gt; required|betweenLen(6,20)|equalWithColumn(password)</p>"
          }
        ]
      }
    },
    "group": "PLAYER",
    "filename": "App/HttpController/User.php",
    "groupTitle": "PLAYER"
  },
  {
    "type": "POST",
    "url": "/api/v1/user/login",
    "title": "login",
    "version": "1.0.0",
    "name": "login",
    "description": "<p>登录</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "phone",
            "description": "<p>REMARK -&gt; 10位的印度手机号码   RULE -&gt; required|length(10)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "password",
            "description": "<p>REMARK -&gt; 平台玩家密码 RULE-&gt; required|betweenLen(min:6_max:20)</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "invite_code",
            "description": "<p>REMARK -&gt; 登录出错次数超过5次时,需要填入验证码.   RULE-&gt; optional</p>"
          }
        ]
      }
    },
    "group": "PLAYER",
    "filename": "App/HttpController/User.php",
    "groupTitle": "PLAYER"
  },
  {
    "type": "POST",
    "url": "/api/v1/user/logout",
    "title": "logout",
    "version": "1.0.0",
    "name": "logout",
    "description": "<p>登出</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "phone",
            "description": "<p>REMARK -&gt; 10位的印度手机号码   RULE -&gt; required|length(10)</p>"
          }
        ]
      }
    },
    "group": "PLAYER",
    "filename": "App/HttpController/User.php",
    "groupTitle": "PLAYER"
  },
  {
    "type": "POST",
    "url": "/api/v1/user/personalDetails",
    "title": "personal_details",
    "version": "1.0.0",
    "name": "personal_details_个人信息修改",
    "description": "<p>流程-&gt;修改信息时无上传图片,则使用系统默认头像。 有上传图片则请求uploadAvatar 返回 url =&gt; 路径信息</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "nickname",
            "description": "<p>REMARK -&gt; 默认为default 用户只能修改一次.参数正常提交   RULE -&gt; required|lengthMax(20)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "realname",
            "description": "<p>REMARK -&gt; 默认为default 用户只能修改一次.参数正常提交   RULE -&gt; required|lengthMax(20)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "facebook",
            "description": "<p>REMARK -&gt; facebook   RULE -&gt; required|lengthMax(200)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "whatsapp",
            "description": "<p>REMARK -&gt; whatsapp   RULE -&gt; required|lengthMax(200)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "avatar",
            "description": "<p>REMARK -&gt; 头像   RULE -&gt; optional</p>"
          }
        ]
      }
    },
    "group": "PLAYER",
    "filename": "App/HttpController/User.php",
    "groupTitle": "PLAYER"
  },
  {
    "type": "GET",
    "url": "/api/v1/user/refresh",
    "title": "refresh",
    "version": "1.0.0",
    "name": "refresh",
    "description": "<p>刷新用户信息</p>",
    "group": "PLAYER",
    "filename": "App/HttpController/User.php",
    "groupTitle": "PLAYER"
  },
  {
    "type": "POST",
    "url": "/api/v1/user/register",
    "title": "register",
    "version": "1.0.0",
    "name": "register",
    "description": "<p>注册</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "phone",
            "description": "<p>REMARK -&gt; 10位的印度手机号码   RULE -&gt; required|length(10)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "password",
            "description": "<p>REMARK -&gt; 平台玩家密码 RULE-&gt; required|betweenLen(min:6_max:20)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "trade_password",
            "description": "<p>REMARK -&gt; 与password密码一致  RULE-&gt; required|betweenLen(min:6_max:20)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "verification",
            "description": "<p>REMARK -&gt; 尚未接入平台,默认为123456  RULE-&gt; required|length(6)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "invite_code",
            "description": "<p>REMARK -&gt; 用户邀请码  RULE-&gt; required</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "whats_app",
            "description": "<p>REMARK -&gt; 可选填,不填默认为default  RULE-&gt; optional</p>"
          }
        ]
      }
    },
    "group": "PLAYER",
    "filename": "App/HttpController/User.php",
    "groupTitle": "PLAYER"
  },
  {
    "type": "GET",
    "url": "/api/v1/user/team",
    "title": "team",
    "version": "1.0.0",
    "name": "team",
    "description": "<p>团队</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "page",
            "description": "<p>REMARK -&gt; 页码 Default -&gt; 1   RULE -&gt; options</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "page_size",
            "description": "<p>REMARK -&gt; 页码当前条数 Default -&gt; 10   RULE -&gt; options</p>"
          }
        ]
      }
    },
    "group": "PLAYER",
    "filename": "App/HttpController/User.php",
    "groupTitle": "PLAYER"
  },
  {
    "type": "GET",
    "url": "/api/v1/user/teamSub",
    "title": "team_sub",
    "version": "1.0.0",
    "name": "team_sub",
    "description": "<p>团队-&gt;查看下级</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "p_id",
            "description": "<p>REMARK -&gt;  列表中ID   RULE -&gt; required|numeric</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "page",
            "description": "<p>REMARK -&gt; 页码 Default -&gt; 1   RULE -&gt; options</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "page_size",
            "description": "<p>REMARK -&gt; 页码当前条数 Default -&gt; 10   RULE -&gt; options</p>"
          }
        ]
      }
    },
    "group": "PLAYER",
    "filename": "App/HttpController/User.php",
    "groupTitle": "PLAYER"
  },
  {
    "type": "POST",
    "url": "/api/v1/user/uploadAvatar",
    "title": "upload_avatar",
    "version": "1.0.0",
    "name": "upload_avatar",
    "description": "<p>流程-&gt;修改信息时无上传图片,则使用系统默认。 有上传图片则请求uploadAvatar</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "File",
            "optional": false,
            "field": "img",
            "description": "<p>REMARK -&gt; File   RULE -&gt; allowFile ['jpg','gif','jpeg','png'] |  FileSize 2m</p>"
          }
        ]
      }
    },
    "group": "PLAYER",
    "filename": "App/HttpController/User.php",
    "groupTitle": "PLAYER"
  },
  {
    "type": "POST",
    "url": "/api/v1/user/withdraw",
    "title": "withdraw",
    "version": "1.0.0",
    "name": "withdraw",
    "description": "<p>提现</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "cash",
            "description": "<p>REMARK -&gt; 提现现金cash,取config配置参数 全局验证  RULE -&gt; optional</p>"
          }
        ]
      }
    },
    "group": "PLAYER",
    "filename": "App/HttpController/User.php",
    "groupTitle": "PLAYER"
  },
  {
    "type": "GET",
    "url": "/api/v1/user/record",
    "title": "record",
    "version": "1.0.0",
    "name": "record",
    "description": "<p>现金(cash)或金币(gold) 存在page &amp; page_size带分页数据返回</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "currency",
            "description": "<p>REMARK -&gt;  default-&gt; cash    gold &amp; cash   RULE -&gt; optional</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "startTime",
            "description": "<p>REMARK -&gt;  default-&gt; 时间缀参考 1609603200       RULE -&gt; optional</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "endTime",
            "description": "<p>REMARK -&gt;  default-&gt; 时间缀参考 1609603200       RULE -&gt; optional</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "page",
            "description": "<p>REMARK -&gt; 页码 Default -&gt; 1   RULE -&gt; options</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "page_size",
            "description": "<p>REMARK -&gt; 页码当前条数 Default -&gt; 10   RULE -&gt; options</p>"
          }
        ]
      }
    },
    "group": "RECORD",
    "filename": "App/HttpController/User.php",
    "groupTitle": "RECORD"
  },
  {
    "type": "GET",
    "url": "/api/v1/user/withdrawRecord",
    "title": "withdraw_record",
    "version": "1.0.0",
    "name": "withdraw_record",
    "description": "<p>提现记录 status-&gt;默认0为正常提交,1成功 2失败</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "startTime",
            "description": "<p>REMARK -&gt;  default-&gt; 时间缀参考 1609603200       RULE -&gt; optional</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "endTime",
            "description": "<p>REMARK -&gt;  default-&gt; 时间缀参考 1609603200       RULE -&gt; optional</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "page",
            "description": "<p>REMARK -&gt; 页码 Default -&gt; 1   RULE -&gt; options</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "page_size",
            "description": "<p>REMARK -&gt; 页码当前条数 Default -&gt; 10   RULE -&gt; options</p>"
          }
        ]
      }
    },
    "group": "RECORD",
    "filename": "App/HttpController/User.php",
    "groupTitle": "RECORD"
  },
  {
    "type": "POST",
    "url": "/api/v1/services/sms",
    "title": "sms",
    "version": "1.0.0",
    "name": "sms",
    "description": "<p>短信发送</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "phone",
            "description": "<p>手机号码    RULE -&gt; required</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"status\" =>  1发送成功 0发送失败 .  120秒内限制发送时间（前端界面倒计时）,6位随机数100000,999999，\n  错误码 1001用于提示手机发送失败,时间间隔过短  本地测试环境验证码为123456\n}",
          "type": "json"
        }
      ]
    },
    "group": "SERVICES",
    "filename": "App/HttpController/Services.php",
    "groupTitle": "SERVICES"
  },
  {
    "type": "POST",
    "url": "/api/v1/task/cashDailyBonus",
    "title": "cash_daily_bonus",
    "version": "1.0.0",
    "name": "cash_daily_bonus",
    "description": "<p>每日签到任务,完成任务达标时 提交任务. 凌晨定时脚本处理签到任务</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  result = >{\n     string ->  The daily check-in task has been completed that day 当天的每日值机任务已完成\n         The daily check-in task has been completed on the day, please submit repeatedly 每日值机任务已于当天完成，请重复提交\n  }\n  \"report_status\" =>  等待凌晨刷新 0只是单独返回 应刷新列表取first-second值判断提交是否可以提交\n}",
          "type": "json"
        }
      ]
    },
    "group": "TASK",
    "filename": "App/HttpController/Task.php",
    "groupTitle": "TASK"
  },
  {
    "type": "GET",
    "url": "/api/v1/task/cashHall",
    "title": "cash_hall",
    "version": "1.0.0",
    "name": "cash_hall",
    "description": "<p>现金大厅任务列表</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"invite_config\": \"二维码邀请 赠送配置 key(任务) value(人数达到) desc (得现金数 送\",\n  \"daily_bonus_config\": \"每日签到配置 key(签到天数) value(当天邀请新朋友个数)  desc(获得现金RS)\",\n  \"invite_sub_num\": \"当前已邀请的人数\",\n  \"invite_receive_status\": \"是否能领取任务状态,能领取则请求cashInvite\",\n  \"report_condition_first\": \"每日签到所需完成条件1\",\n  \"report_condition_second\": \"每日签到所需完成条件2\",\n  \"report_value\": \"当前档位所需邀请人数\",\n  \"report_desc\": \"当前档位所赠送的现金\",\n  \"report_status\": \"first_secoond为1情况下,可以请求cashDailyBonus接口 获取现金\",\n  \"report_data\": \"是否存在签到数据,定时凌晨处理自增或者delete新的周期\",\n}",
          "type": "json"
        }
      ]
    },
    "group": "TASK",
    "filename": "App/HttpController/Task.php",
    "groupTitle": "TASK"
  },
  {
    "type": "POST",
    "url": "/api/v1/task/cashInvite",
    "title": "cash_invite",
    "version": "1.0.0",
    "name": "cash_invite",
    "description": "<p>invite_receive_status为1时提交任务处理,自动领取最近的一条达标邀请任务</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"invite_receive_status\" =>  1还可以继续领取 0为已不能领取\n}",
          "type": "json"
        }
      ]
    },
    "group": "TASK",
    "filename": "App/HttpController/Task.php",
    "groupTitle": "TASK"
  },
  {
    "type": "GET",
    "url": "/api/v1/task/category",
    "title": "category",
    "version": "1.0.0",
    "name": "category",
    "description": "<p>任务分类,图标显示.</p>",
    "group": "TASK",
    "filename": "App/HttpController/Task.php",
    "groupTitle": "TASK"
  },
  {
    "type": "GET",
    "url": "/api/v1/task/list",
    "title": "list",
    "version": "1.0.0",
    "name": "list",
    "description": "<p>任务列表-&gt; 从任务分类获取对应的task_id传入对应分类</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n \"code\" : 200,\n \"result\" :{\n \"page\" : \"1\",\n \"page_size\" : \"10\":\n \"totalNum\" : \"总条数\",\n \"results\" : {\n  \"id\": \"自增ID,用于传入领取任务\",\n  \"title\": \"标题\",\n  \"info\": \"简介\",\n  \"content\": \"内容\",\n  \"total_price\": \"任务总价\",\n  \"total_number\": \"任务数量\",\n  \"receive_number\": \"已领取的任务数量\",\n  \"link_info\": \"链接信息\",\n  \"link_info\": \"截止日期\",\n  \"finish_condition\": \"完成条件\",\n  \"created_at\": \"创建时间\",\n  \"task_user_satus\" : 0未领取  1：进行中；2：审核中；3：已完成；4：已失败;5:恶意',\n  \"taskGroup\" : {\n         当前任务组的栏目图标与图片,获取不涉及状态\n         }\n     }\n },\n \"msg\" : success,\n}",
          "type": "json"
        }
      ]
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "task_id",
            "description": "<p>REMARK -&gt; 任务分类ID   RULE -&gt; required</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "page",
            "description": "<p>REMARK -&gt; 页码 Default -&gt; 1   RULE -&gt; options</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "page_size",
            "description": "<p>REMARK -&gt; 页码当前条数 Default -&gt; 10   RULE -&gt; options</p>"
          }
        ]
      }
    },
    "group": "TASK",
    "filename": "App/HttpController/Task.php",
    "groupTitle": "TASK"
  },
  {
    "type": "POST",
    "url": "/api/v1/task/submitGiveUp",
    "title": "submit_give_up",
    "version": "1.0.0",
    "name": "submit_give_up",
    "description": "<p>任务放弃</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "task_id",
            "description": "<p>REMARK -&gt; task_id 任务ID  RULE -&gt; required|numeric</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"task_id\": \"任务ID\",\n  \"status\" =>  任务的状态。1：进行中；2：审核中；3：已完成；4：已失败;5:恶意'\n}",
          "type": "json"
        }
      ]
    },
    "group": "TASK",
    "filename": "App/HttpController/Task.php",
    "groupTitle": "TASK"
  },
  {
    "type": "POST",
    "url": "/api/v1/task/submitTask",
    "title": "submit_task",
    "version": "1.0.0",
    "name": "submit_task",
    "description": "<p>任务提交</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "task_id",
            "description": "<p>REMARK -&gt; task_id 任务ID  RULE -&gt; required|numeric</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "img",
            "description": "<p>REMARK -&gt; img 图片链接  RULE -&gt; required|lengthMax(200)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>REMARK -&gt; content 提交内容  RULE -&gt; required|lengthMax(500)</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"task_id\": \"任务ID\",\n  \"status\" =>  任务的状态。1：进行中；2：审核中；3：已完成；4：已失败;5:恶意'\n}",
          "type": "json"
        }
      ]
    },
    "group": "TASK",
    "filename": "App/HttpController/Task.php",
    "groupTitle": "TASK"
  },
  {
    "type": "POST",
    "url": "/api/v1/task/submitTaskImg",
    "title": "submit_task_img",
    "version": "1.0.0",
    "name": "submit_task_img",
    "description": "<p>上传任务图片说明</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "File",
            "optional": false,
            "field": "img",
            "description": "<p>REMARK -&gt; File   RULE -&gt; allowFile ['jpg','gif','jpeg','png'] |  FileSize 2m</p>"
          }
        ]
      }
    },
    "group": "TASK",
    "filename": "App/HttpController/Task.php",
    "groupTitle": "TASK"
  },
  {
    "type": "POST",
    "url": "/api/v1/task/get",
    "title": "task_get",
    "version": "1.0.0",
    "name": "task_get",
    "description": "<p>领取任务列表ID传入,同个任务只能领取一次.不能重复领取</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "task_id",
            "description": "<p>REMARK -&gt; 任务列表ID   RULE -&gt; required</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"task_id\": \"任务ID\",\n  \"status\" =>  任务的状态。1：进行中；2：审核中；3：已完成；4：已失败;5:恶意'\n}",
          "type": "json"
        }
      ]
    },
    "group": "TASK",
    "filename": "App/HttpController/Task.php",
    "groupTitle": "TASK"
  },
  {
    "type": "GET",
    "url": "/api/v1/task/user",
    "title": "task_user",
    "version": "1.0.0",
    "name": "task_user",
    "description": "<p>当前登录用户,已领取的任务列表.</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "page",
            "description": "<p>REMARK -&gt; 页码 Default -&gt; 1   RULE -&gt; options</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": true,
            "field": "page_size",
            "description": "<p>REMARK -&gt; 页码当前条数 Default -&gt; 10   RULE -&gt; options</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"status\": \"1\", //任务的状态。1：进行中；2：审核中；3：已完成；4：已失败;5:恶意',\n  \"lastname\": \"Doe\"\n}",
          "type": "json"
        }
      ]
    },
    "group": "TASK",
    "filename": "App/HttpController/Task.php",
    "groupTitle": "TASK"
  }
] });
