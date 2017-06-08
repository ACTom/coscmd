<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class CpCommand extends Command {

    protected function configure() {
        $this
            ->setName('cp')
            ->setDescription('Copy files')
            ->setHelp('This command allows you to copy file from a path to another')
            ->addArgument('source', InputArgument::REQUIRED, 'Which file would you like to copy?')
            ->addArgument('destnation', InputArgument::REQUIRED, 'Where would you like to copy to?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $source = $input->getArgument('source');
        $destnation = $input->getArgument('destnation');
        $this->doAction($source, $destnation, $output);
    }
    
    public function doAction($source, $destnation, OutputInterface $output) {
        global $handle;
        if (!$handle->copyFile($source, $destnation)) {
            $errorNo = $handle->getLastErrorNo();
            $errorMsg = $handle->getLastError();
            $output->writeln("cp: copy {$source} to {$destnation}: fail, error code:{$errorNo}, error message: {$errorMsg}");
        }
    }
}