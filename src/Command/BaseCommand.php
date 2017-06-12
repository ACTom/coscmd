<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use ACTom\COSCmd\Cos;
use ACTom\COSCmd\Utils;
use Phar;


class BaseCommand extends Command {
    private $config = [];
    private $handle = null;
    
    protected function getHandle() {
        if ($this->handle === null) {
            $config = $this->getConfig();
            $this->handle = new Cos($config);
        }
        return $this->handle;
    }
    
    private function getConfig() {
        if (!$this->config) {
            $input = new ArgvInput(null, $this->getDefinition());
            $errorOutput = (new ConsoleOutput())->getErrorOutput();
            $config = $input->getOption('config');
            /* 配置文件存在 */
            if ($config !== '') {
                $config = Utils::localPath($config);
                if (!file_exists($config)) {
                    $errorOutput->writeln("error: Configure file {$config} does not exists.");
                    exit();
                }
            } else {
                /* 用户目录下.coscmd.conf */
                $user = getenv('HOME') . DIRECTORY_SEPARATOR . '.coscmd.conf';
                $system = '/etc/coscmd.conf';
                $local = (Phar::running(false) ? dirname(Phar::running(false)) : APP_PATH) . DIRECTORY_SEPARATOR . 'config.php';
                if (file_exists($user)) {
                    $config = $user;
                /* 系统目录下coscmd.conf */
                } elseif (file_exists($system)) {
                    $config = $system;
                /* 当前目录下config.php */
                } elseif (file_exists($local)){
                    $config = $local;
                }
                if ($config === '') {
                    $errorOutput->writeln("error: Cannot find configure file, your can put configure file to one of ({$user}, {$system}, {$local}).");
                    exit();
                }
            }
            $this->config = require $config;
        }
        return $this->config;
    }
    
}