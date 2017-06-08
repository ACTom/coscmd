<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class LsCommand extends Command {

    protected function configure() {
        $this
            ->setName('ls')
            ->setAliases(['dir'])
            ->setDescription('List directory contents')
            ->setHelp('Displays the infomation of files within the directory')
            ->addArgument('directory', InputArgument::OPTIONAL, 
                'Which directory would you like to display?', '/')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $directory = $input->getArgument('directory');
        $this->doAction($directory, $output);
    }
    
    public function doAction($directory, OutputInterface $output) {
        global $handle;
        $result = $handle->listDirectory($directory);
        array_walk($result, function($item) use (&$output){
            $output->writeln($item['name']);
        });
    }
}