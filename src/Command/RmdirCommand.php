<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class RmdirCommand extends Command {

    protected function configure() {
        $this
            ->setName('rmdir')
            ->setDescription('Remove empty directory')
            ->setHelp('Remove empty directory')
            ->addArgument('directory', InputArgument::REQUIRED, 
                'Which directory would you like to remove?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $directory = $input->getArgument('directory');
        $this->doAction($directory, $output);
    }
    
    public function doAction($directory, OutputInterface $output) {
        global $handle;
        if (!$handle->deleteDirectory($directory)) {
            $errorNo = $handle->getLastErrorNo();
            $errorMsg = $handle->getLastError();
            $output->writeln("rmdir: failed to remove '{$directory}': error code:{$errorNo}, error message: {$errorMsg}");
        }
    }
}