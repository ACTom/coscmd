<?php

require __DIR__ . '/vendor/autoload.php';

use ACTom\COSCmd\Application;
use ACTom\COSCmd\Cos;
use ACTom\COSCmd\Utils;

/* 设置全局常量 */
define('APP_PATH', __DIR__);

/* 检查运行环境是否满足 */
Utils::checkRequirement();

/* 支持的命令 */
$commands = ['ls', 'mv', 'rmdir', 'mkdir', 'rm', 'cp', 'push', 'pull'];

/* 设置程序信息 */
$application = new Application();
$application->loadCommands($commands);
$application->run();