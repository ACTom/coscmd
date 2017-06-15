<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use ACTom\COSCmd\Utils;


class RmCommand extends BaseCommand {
    private $options = [];

    protected function configure() {
        $this
            ->setName('rm')
            ->setDescription('Remove file')
            ->setHelp('Remove file')
            ->addArgument('file', InputArgument::REQUIRED, 
                'Which file would you like to remove?')
            ->addOption('recursive', ['r', 'R'], InputOption::VALUE_NONE, 'Remove directories and their contents recursively.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $file = $input->getArgument('file');
        $this->doAction($file, $input, $output);
    }
    
    public function doAction($file, InputInterface $input, OutputInterface $output) {
        $handle = $this->getHandle();
        $file = Utils::normalizerRemotePath($file);
        $errorOutput = $output->getErrorOutput();
        $this->options = $input->getOptions();
        if (!$handle->fileExists($file)) {
            $errorOutput->writeln("<error>rm: cannot remove '{$file}': No such file or directory</>");
            return ;
        }
        if ($handle->isDirectory($file)) {
            if (!$this->options['recursive']) {
                $errorOutput->writeln("<error>rm: cannot remove '{$file}': Is a directory</>");
                return ;
            }
            $this->deleteDirectory($file, $output);
        } else {
            $this->deleteFile($file, $output);
        }
    }
    
    private function deleteDirectory($file, OutputInterface $output) {
        $handle = $this->getHandle();
        $errorOutput = $output->getErrorOutput();
        $target = $source = [[
            'source' => $file,
            'isDirectory' => true
        ]];
        if ($this->options['verbose']) {
            $output->writeln('Searching directoies');
        }
        while (($item = array_pop($source))) {
            if ($this->options['verbose']) {
                $output->writeln("Searching: {$item['source']}");
            }
            $list = $handle->listDirectory($item['source'], true);
            foreach ($list as $aFile) {
                $fileInfo = [
                    'source' => $item['source'] . '/' . $aFile['name'],
                    'isDirectory' => $aFile['isDirectory']
                ];
                if ($aFile['name'] === '.' || $aFile['name'] === '..') {
                    if ($fileInfo['isDirectory']) {
                        $handle->deleteDirectory($fileInfo['source']);
                    } else {
                        $handle->deleteFile($fileInfo['source']);
                    }
                    continue;
                }
                if ($fileInfo['isDirectory']) {
                    array_push($source, $fileInfo);
                }
                array_push($target, $fileInfo);
            }
        }
        $count = 0;
        $maxCount = count($target);
        while (($item = array_pop($target))) {
            $count ++;
            if ($item['isDirectory']) {
                if ($this->options['verbose']) {
                    $output->writeln("[{$count}/{$maxCount}] Deleting Directory: {$item['source']}");
                }
                if (!$handle->deleteDirectory($item['source'])) {
                    $errorNo = $handle->getLastErrorNo();
                    $errorMsg = $handle->getLastError();
                    $errorOutput->writeln("<error>rm: cannot remove '{$item['source']}': error code:{$errorNo}, error message: {$errorMsg}</>");
                }
            } else {
                if ($this->options['verbose']) {
                    $output->writeln("[{$count}/{$maxCount}] Deleting File: {$item['source']}");
                }
                $this->deleteFile($item['source'], $output);
            }
        }
    }
    
    private function deleteFile($file, OutputInterface $output) {
        $handle = $this->getHandle();
        $errorOutput = $output->getErrorOutput();
        if (!$handle->deleteFile($file)) {
            $errorNo = $handle->getLastErrorNo();
            $errorMsg = $handle->getLastError();
            $errorOutput->writeln("<error>rm: cannot remove '{$file}': error code:{$errorNo}, error message: {$errorMsg}</error>");
        }
    }
}