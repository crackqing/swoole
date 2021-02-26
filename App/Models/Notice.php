<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

class Notice extends AbstractModel
{
    const NOTICE_GROUPID_NEW = 3;

    const NOTICE_STATE_DEFAULT = 0;
    const NOTICE_STATE_CLOSE = 1;

    protected $tableName = 'notice';
    
    protected function getCoverImgAttr($value, $data)
    {
        return DOMAIN_ADMIN.$value;
    }
}
