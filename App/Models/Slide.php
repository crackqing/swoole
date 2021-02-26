<?php
namespace App\Models;

use EasySwoole\ORM\AbstractModel;

class Slide extends AbstractModel
{
    const SLIDE_STATUS_DEFAULT = 0;
    const SLIDE_STATUS_CLOSE = 1;

    protected $tableName = 'slide';

    protected function getImgPathAttr($value, $data)
    {
        return DOMAIN_ADMIN.$value;
    }
}
