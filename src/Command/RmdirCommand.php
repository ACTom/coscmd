<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use ACTom\COSCmd\Utils;


class RmdirCommand extends BaseCommand {

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
        $this->doAction($directory, $input, $output);
    }
    
    public function doAction($directory, InputInterface $input, OutputInterface $output) {
        $handle = $this->getHandle();
        $directory = Utils::normalizerRemotePath($directory);
        $errorOutput = $output->getErrorOutput();
        if (!$handle->deleteDirectory($directory)) {
            $errorNo = $handle->getLastErrorNo();
            $errorMsg = $handle->getLastError();
            $errorOutput->writeln("<error>rmdir: failed to remove '{$directory}': error code:{$errorNo}, error message: {$errorMsg}</>");
        }
    }
}