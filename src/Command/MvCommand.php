<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class MvCommand extends BaseCommand {

    protected function configure() {
        $this
            ->setName('mv')
            ->setDescription('Move files')
            ->setHelp('This command allows you to move file from a path to another')
            ->addArgument('source', InputArgument::REQUIRED, 'Which file would you like to move?')
            ->addArgument('destnation', InputArgument::REQUIRED, 'Where would you like to move to?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $source = $input->getArgument('source');
        $destnation = $input->getArgument('destnation');
        $this->doAction($source, $destnation, $input, $output);
    }
    
    public function doAction($source, $destnation, InputInterface $input, OutputInterface $output) {
        $handle = $this->getHandle();
        if (!$handle->moveFile($source, $destnation)) {
            $errorNo = $handle->getLastErrorNo();
            $errorMsg = $handle->getLastError();
            $output->writeln("mv: rename {$source} to {$destnation}: fail, error code:{$errorNo}, error message: {$errorMsg}");
        }
    }
}