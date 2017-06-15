<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use ACTom\COSCmd\Utils;


class MvCommand extends BaseCommand {

    private $options = [];

    protected function configure() {
        $this
            ->setName('mv')
            ->setDescription('Move files')
            ->setHelp('This command allows you to move file from a path to another')
            ->addArgument('source', InputArgument::REQUIRED, 'Which file would you like to move?')
            ->addArgument('destnation', InputArgument::REQUIRED, 'Where would you like to move to?')
            ->addOption('recursive', ['r', 'R'], InputOption::VALUE_NONE, 'Remove directories and their contents recursively.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $source = $input->getArgument('source');
        $destnation = $input->getArgument('destnation');
        $this->doAction($source, $destnation, $input, $output);
    }
    
    public function doAction($source, $destnation, InputInterface $input, OutputInterface $output) {
        $handle = $this->getHandle();
        $this->options = $input->getOptions();
        $errorOutput = $output->getErrorOutput();
        $source = Utils::normalizerRemotePath($source);
        $destnation = Utils::normalizerRemotePath($destnation);
        
        if (!$handle->fileExists($source)) {
            $errorOutput->writeln("<error>mv: {$source} does not exists</>");
            return ;
        }
        if ($handle->isDirectory($destnation)) {
            $basename = basename($source);
            $destnation = $destnation . '/' . $basename;
        }
        if ($handle->isDirectory($source)) {
            if (!$this->options['recursive']) {
                $errorOutput->writeln("<error>mv: cannot copy '{$source}': Is a directory</>");
                return ;
            }
            if ($handle->fileExists($destnation) && !$handle->isDirectory($destnation)) {
                $errorOutput->writeln("<error>mv: cannot overwrite non-directory {$destnation} with directory {$source}</>");
                return ;
            }
            $this->moveDirectory($source, $destnation, $input, $output);
        } else {
            $this->moveFile($source, $destnation, $input, $output);
        }
    }
    
    private function moveDirectory($source, $destnation, InputInterface $input, OutputInterface $output) {
        $handle = $this->getHandle();
        $errorOutput = $output->getErrorOutput();
        $fileInfo = [
            'source' => $source,
            'destnation' => $destnation,
            'isDirectory' => true
        ];
        /* $source 用来遍历目录，遍历同时创建远程目录
         * $target 用来记录需要下载的文件，仅记录文件
         */
        $source = $target = $delete = [];
        if ($this->options['verbose']) {
            $output->writeln('Searching directoies');
        }
        /* 目标文件夹创建成功才继续 */
        if ($handle->createDirectory($destnation)) {
            $source = $delete = [$fileInfo];
        } else {
            $errorOutput->writeln("<error>mv: Create Directory {$destnation} fail</>");
        }
        while (($item = array_pop($source))) {
            if ($this->options['verbose']) {
                $output->writeln("Searching: {$item['source']}");
            }
            $list = $handle->listDirectory($item['source'], true);
            foreach ($list as $aFile) {
                $fileInfo = [
                    'source' => $item['source'] . '/' . $aFile['name'],
                    'destnation' => $item['destnation'] . '/' . $aFile['name'],
                    'isDirectory' => $aFile['isDirectory']
                ];
                if ($aFile['name'] === '.' || $aFile['name'] === '..') {
                    continue;
                }
                /* 目标文件夹创建成功才继续 */
                if ($fileInfo['isDirectory'] ) {
                    if ($handle->createDirectory($fileInfo['destnation'])) {
                        if ($this->options['verbose']) {
                            $output->writeln("Create Directory {$fileInfo['destnation']} success");
                        }
                        array_push($source, $fileInfo);
                        array_push($delete, $fileInfo);
                    } else {
                        $errorOutput->writeln("<error>mv: Create Directory {$fileInfo['destnation']} fail</>");
                    }
                }
                if (!$fileInfo['isDirectory']) {
                    array_push($target, $fileInfo);
                }
            }
        }
        /* 移动文件 */
        $count = 0;
        $maxCount = count($target);
        while (($item = array_pop($target))) {
            $count ++;
            if ($this->options['verbose']) {
                $output->writeln("[{$count}/{$maxCount}] Moving File: {$item['source']} to {$item['destnation']}");
            }
            $this->moveFile($item['source'], $item['destnation'], $input, $output);
        }
        /* 删除文件夹 */
        $count = 0;
        $maxCount = count($delete);
        while (($item = array_pop($delete))) {
            $count ++;
            if ($this->options['verbose']) {
                $output->writeln("[{$count}/{$maxCount}] Deleting Directory: {$item['source']}");
            }
            if (!$handle->deleteDirectory($item['source'])) {
                $errorNo = $handle->getLastErrorNo();
                $errorMsg = $handle->getLastError();
                $errorOutput->writeln("<error>mv: delete {$source}: fail, error code:{$errorNo}, error message: {$errorMsg}</>");
            }
        }
    }
    
    private function moveFile($source, $destnation, InputInterface $input, OutputInterface $output) {
        $handle = $this->getHandle();
        $errorOutput = $output->getErrorOutput();
        if (!$handle->moveFile($source, $destnation)) {
            $errorNo = $handle->getLastErrorNo();
            $errorMsg = $handle->getLastError();
            $errorOutput->writeln("<error>mv: move {$source} to {$destnation}: fail, error code:{$errorNo}, error message: {$errorMsg}</>");
        }
    }
}