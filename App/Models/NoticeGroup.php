<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

class NoticeGroup extends AbstractModel
{
    const NOTICE_GROUP_INDEX = 4; //首页公告
    const NOTICE_GROUP_MARQUEE = 3; //跑马灯

    
    const NOTICE_GROUP_ACTIVITY = 5; //公告活动图上传处理
    const NOTICE_GROUP_SLIDE = 6; //首页轮播数据显示

    protected $tableName = 'notice_group';
}
