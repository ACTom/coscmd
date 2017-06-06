<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use QCloud\Cos\Api;


class MoveCommand extends Command {

    protected function configure() {
        $this
            ->setName('move')
            ->setAliases(['mv'])
            ->setDescription('Move File in COS')
            ->setHelp('This command allows you to move file from a path to another')
            ->addArgument('source', InputArgument::REQUIRED, 'Which file would you like to move?')
            ->addArgument('destnation', InputArgument::REQUIRED, 'Where would you like to move to?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
    }
    
    public function doAction($source, $destnation) {
        global $config;
        $api = new Api($config);
        $api->moveFile($config['bucket'], $source, $destnation);
    }
}