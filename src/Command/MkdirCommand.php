<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class MkdirCommand extends BaseCommand {

    protected function configure() {
        $this
            ->setName('mkdir')
            ->setDescription('Create directory')
            ->setHelp('Create directory')
            ->addArgument('directory', InputArgument::REQUIRED, 
                'Which directory would you like to create?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $directory = $input->getArgument('directory');
        $this->doAction($directory, $input, $output);
    }
    
    public function doAction($directory, InputInterface $input, OutputInterface $output) {
        $handle = $this->getHandle();
        $errorOutput = $output->getErrorOutput();
        if (!$handle->createDirectory($directory)) {
            $errorNo = $handle->getLastErrorNo();
            $errorMsg = $handle->getLastError();
            $errorOutput->writeln("<error>mkdir: cannot create directory '{$directory}': error code:{$errorNo}, error message: {$errorMsg}</>");
        }
    }
}