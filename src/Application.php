<?php

namespace ACTom\COSCmd;

use Symfony\Component\Console\Application as App;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Phar;

class Application extends App {
    private $config = [];
    private $input = null;
    private $output = null;
    
    public function __construct() {
        parent::__construct('CosCmd', 'v0.0.1');
        $this->getDefinition()->addOption(new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, "Load a configure file", ""));
        
        $this->input = new ArgvInput();
        $this->input->bind($this->getDefinition());
        $this->output = new ConsoleOutput();
    }
    
    public function loadConfigure() {
        global $handle;
        $errorOutput = $this->output->getErrorOutput();
        $config = $this->input->getOption('config');
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
        $handle = new Cos($this->config);
    }
    
    public function loadCommands($commands) {
        foreach ($commands as $command) {
            $command = ucfirst($command);
            $commandName = "\\ACTom\\COSCmd\\Command\\{$command}Command";
            $this->add(new $commandName());
        }
    }
}