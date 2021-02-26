<?php
namespace Tests;

use EasySwoole\Mysqli\QueryBuilder;
use PHPUnit\Framework\TestCase;
use EasySwoole\ORM\DbManager;

class DbTest extends TestCase
{
    public function testCon()
    {
        $builder = new QueryBuilder();
        $builder->raw('select version()');
        $ret = DbManager::getInstance()->query($builder, true)->getResult();
        $this->assertArrayHasKey('version()', $ret[0]);
    }
}
