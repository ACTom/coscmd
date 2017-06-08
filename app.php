<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use ACTom\COSCmd\Cos;

$configDirectory = Phar::running(false) ? dirname(Phar::running(false)) : __DIR__;
$config = require $configDirectory . '/config.php';
$handle = new Cos($config);


$commands = ['ls', 'mv', 'rmdir', 'mkdir', 'rm', 'cp', 'push', 'pull'];

$application = new Application();
$application->setName('Tencent Cloud COS Command Tool');
$application->setVersion('v0.0.1');
foreach ($commands as $command) {
    $command = ucfirst($command);
    $commandName = "\\ACTom\\COSCmd\\Command\\{$command}Command";
    $application->add(new $commandName());
}
$application->run();