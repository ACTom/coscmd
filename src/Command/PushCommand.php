<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use ACTom\COSCmd\Utils;


class PushCommand extends Command {

    protected function configure() {
        $this
            ->setName('push')
            ->setDescription('Upload file to cos')
            ->setHelp('This command allows you to upload file to cos')
            ->addArgument('source', InputArgument::REQUIRED, 'Which file would you like to upload?')
            ->addArgument('destnation', InputArgument::REQUIRED, 'Where would you like to upload to?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $source = $input->getArgument('source');
        $destnation = $input->getArgument('destnation');
        $this->doAction($source, $destnation, $output);
    }
    
    public function doAction($source, $destnation, OutputInterface $output) {
        global $handle;
        $destnation = $this->normalizerDestnation($source, $destnation);
        if (!$handle->uploadFile($source, $destnation)) {
            $errorNo = $handle->getLastErrorNo();
            $errorMsg = $handle->getLastError();
            $output->writeln("push: upload file {$source} to {$destnation}: fail, error code:{$errorNo}, error message: {$errorMsg}");
        }
    }
    
    private function normalizerDestnation($source, $destnation) {
        global $handle;
        $destnation = Utils::normalizerRemotePath($destnation);
        if ($handle->isDirectory($destnation)) {
            $basename = basename($source);
            $destnation = $destnation . '/' . $basename;
        }
        return $destnation;
    }
}