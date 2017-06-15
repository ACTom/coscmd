<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use ACTom\COSCmd\Utils;


class PullCommand extends BaseCommand {
    private $options = [];

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
        $this->doAction($source, $destnation, $input, $output);
    }
    
    public function doAction($source, $destnation, InputInterface $input, OutputInterface $output) {
        $handle = $this->getHandle();
        $this->options = $input->getOptions();
        $errorOutput = $output->getErrorOutput();
        $source = Utils::normalizerRemotePath($source);
        $destnation = Utils::normalizerLocalPath($destnation);
        if (is_dir($destnation)) {
            $basename = basename($source);
            $destnation .= DIRECTORY_SEPARATOR . $basename;
        }
        
        if (!$handle->fileExists($source)) {
            $errorOutput->writeln("pull: cannot pull {$source}, No such file or directory");
            return ;
        }
        
        if ($handle->isDirectory($source)) {
            $this->downloadDirectory($source, $destnation, $input, $output);
        } else {
            $this->downloadFile($source, $destnation, $input, $output);
        }
    }
    
    private function downloadDirectory($source, $destnation, InputInterface $input, OutputInterface $output) {
        $handle = $this->getHandle();
        $errorOutput = $output->getErrorOutput();
        $fileInfo = [
            'source' => $source,
            'destnation' => $destnation,
            'isDirectory' => true
        ];
        /* $source 用来遍历目录，遍历同时创建本地目录
         * $target 用来记录需要下载的文件，仅记录文件
         */
        $source = $target = [];
        if ($this->options['verbose']) {
            $output->writeln('Searching directoies');
        }
        /* 目标文件夹创建成功才继续 */
        if (mkdir($destnation)) {
            $source = [$fileInfo];
        } else {
            $errorOutput->writeln("pull: Create Directory {$destnation} fail");
        }
        while (($item = array_pop($source))) {
            if ($this->options['verbose']) {
                $output->writeln("Searching: {$item['source']}");
            }
            $list = $handle->listDirectory($item['source'], true);
            foreach ($list as $aFile) {
                $fileInfo = [
                    'source' => $item['source'] . '/' . $aFile['name'],
                    'destnation' => $item['destnation'] . DIRECTORY_SEPARATOR . $aFile['name'],
                    'isDirectory' => $aFile['isDirectory']
                ];
                if ($aFile['name'] === '.' || $aFile['name'] === '..') {
                    continue;
                }
                /* 目标文件夹创建成功才继续 */
                if ($fileInfo['isDirectory'] ) {
                    if (mkdir($fileInfo['destnation'])) {
                        if ($this->options['verbose']) {
                            $output->writeln("Create Directory {$item['destnation']} success");
                        }
                        array_push($source, $fileInfo);
                    } else {
                        $errorOutput->writeln("pull: Create Directory {$item['destnation']} fail");
                    }
                }
                if (!$fileInfo['isDirectory']) {
                    array_push($target, $fileInfo);
                }
            }
        }
        $count = 0;
        $maxCount = count($target);
        while (($item = array_pop($target))) {
            $count ++;
            if ($this->options['verbose']) {
                $output->writeln("[{$count}/{$maxCount}] Downloading File: {$item['source']}");
            }
            $this->downloadFile($item['source'], $item['destnation'], $input, $output);
        }
    }
    
    private function downloadFile($source, $destnation, InputInterface $input, OutputInterface $output) {
        $handle = $this->getHandle();
        $errorOutput = $output->getErrorOutput();
        if (!$handle->downloadFile($source, $destnation)) {
            $errorNo = $handle->getLastErrorNo();
            $errorMsg = $handle->getLastError();
            $errorOutput->writeln("pull: download file {$source} to {$destnation}: fail, error code:{$errorNo}, error message: {$errorMsg}");
        }
    }
}