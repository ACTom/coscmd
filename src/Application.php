<?php

namespace ACTom\COSCmd;

use Symfony\Component\Console\Application as App;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Phar;

class Application extends App {
    
    public function __construct() {
        parent::__construct('CosCmd', 'v0.5.0');
        $definition = $this->getDefinition();
        $definition->addOption(new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, "Load a configure file", ""));
    }
    
    public function loadCommands($commands) {
        foreach ($commands as $command) {
            $command = ucfirst($command);
            $commandName = "\\ACTom\\COSCmd\\Command\\{$command}Command";
            $this->add(new $commandName());
        }
    }
}