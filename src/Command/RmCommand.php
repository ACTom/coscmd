<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class RmCommand extends BaseCommand {

    protected function configure() {
        $this
            ->setName('rm')
            ->setDescription('Remove file')
            ->setHelp('Remove file')
            ->addArgument('file', InputArgument::REQUIRED, 
                'Which file would you like to remove?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $file = $input->getArgument('file');
        $this->doAction($file, $output);
    }
    
    public function doAction($file, OutputInterface $output) {
        $handle = $this->getHandle();
        if (!$handle->deleteFile($file)) {
            $errorNo = $handle->getLastErrorNo();
            $errorMsg = $handle->getLastError();
            $output->writeln("rm: failed to remove '{$directory}': error code:{$errorNo}, error message: {$errorMsg}");
        }
    }
}