<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use ACTom\COSCmd\Cos;
use ACTom\COSCmd\Utils;

/* 检查运行环境是否满足 */
Utils::checkRequirement();

/* 载入配置文件 */
$configDirectory = Phar::running(false) ? dirname(Phar::running(false)) : __DIR__;
$config = require $configDirectory . '/config.php';
$handle = new Cos($config);

/* 支持的命令 */
$commands = ['ls', 'mv', 'rmdir', 'mkdir', 'rm', 'cp', 'push', 'pull'];

/* 设置程序信息 */
$application = new Application();
$application->setName('Tencent Cloud COS Command Tool');
$application->setVersion('v0.0.1');
foreach ($commands as $command) {
    $command = ucfirst($command);
    $commandName = "\\ACTom\\COSCmd\\Command\\{$command}Command";
    $application->add(new $commandName());
}
$application->run();