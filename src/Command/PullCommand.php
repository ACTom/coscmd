<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use ACTom\COSCmd\Utils;


class PullCommand extends Command {

    protected function configure() {
        $this
            ->setName('pull')
            ->setDescription('Download file from cos')
            ->setHelp('This command allows you to download file from cos')
            ->addArgument('source', InputArgument::REQUIRED, 'Which file would you like to download?')
            ->addArgument('destnation', InputArgument::REQUIRED, 'Where would you like to download to?')
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
        if (!$handle->downloadFile($source, $destnation)) {
            $errorNo = $handle->getLastErrorNo();
            $errorMsg = $handle->getLastError();
            $output->writeln("pull: download file {$source} to {$destnation}: fail, error code:{$errorNo}, error message: {$errorMsg}");
        }
    }
    
    private function normalizerDestnation($source, $destnation) {
        global $handle;
        $source = Utils::normalizerRemotePath($source);
        if (is_dir($destnation)) {
            $basename = basename($source);
            $destnation = $destnation . '/' . $basename;
        }
        return $destnation;
    }
}