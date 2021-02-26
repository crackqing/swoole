<?php
namespace App\Command;

use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\Command\AbstractInterface\CommandHelpInterface;

class Tool implements CommandInterface
{
    public function commandName(): string
    {
        return 'JK_Tool';
    }

    public function exec(): ?string
    {
        //打印参数,打印测试值
        return '';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        try {
            $commandHelpTest = (new class implements CommandHelpInterface {
                public function addAction(string $actionName, string $desc)
                {
                }
                public function addActionOpt(string $actionOptName, string $desc)
                {
                }
            });
            //doing

            if ($commandHelpTest instanceof CommandHelpInterface) {
                return $commandHelp;
            }
        } catch (\Throwable $th) {
            //throw $th;
            var_dump($th);
        }
    }

    public function desc(): string
    {
        return '';
    }
}
