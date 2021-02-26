<?php

namespace App\Log;

use EasySwoole\Log\LoggerInterface;

//进程日志统一写入处理,当前版本无法写入
class ProcessHandel implements LoggerInterface
{
    private $logDir;

    #use Singleton;

    public function __construct(string $logDir = null)
    {
        if (empty($logDir)) {
            $logDir = getcwd();
        }
        $this->logDir = $logDir;
    }

    public function log(?string $msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'debug'):string
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        //按MD来存放对应日志
        $YM = date('md');
        $dir = $this->logDir.$YM.'/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $filename = "process_{$category}.log";
        $filePath = $dir.$filename;

        $str = "PROCESS:[{$date}][{$category}][{$levelStr}] : [{$msg}]\n";
        file_put_contents($filePath, "{$str}", FILE_APPEND|LOCK_EX);

        return $str;
    }

    public function console(?string $msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'console')
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $temp = "PROCESS:[{$date}][{$category}][{$levelStr}]:[{$msg}]\n";
        fwrite(STDOUT, $temp);
    }

    private function levelMap(int $level)
    {
        switch ($level) {
            case self::LOG_LEVEL_INFO:
                return 'info';
            case self::LOG_LEVEL_NOTICE:
                return 'notice';
            case self::LOG_LEVEL_WARNING:
                return 'warning';
            case self::LOG_LEVEL_ERROR:
                return 'error';
            default:
                return 'unknown';
        }
    }
}
