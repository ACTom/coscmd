<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


class LsCommand extends Command {

    protected function configure() {
        $this
            ->setName('ls')
            ->setAliases(['dir'])
            ->setDescription('List directory contents')
            ->setHelp('Displays the infomation of files within the directory')
            ->addArgument('directory', InputArgument::IS_ARRAY, 
                'Which directory would you like to display?', ['/'])
            ->addOption('long', 'l', InputOption::VALUE_NONE, 'use a long listing format')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $directory = $input->getArgument('directory');
        $option = $input->getOptions();
        $this->doAction($directory, $option, $output);
    }
    
    public function doAction($directories, $option, OutputInterface $output) {
        $list = $this->getFileList($directories, $option, $output);
        $this->printDirectory($list, $option, $output);
    }
    
    private function getFileList($directories, $option, OutputInterface $output) {
        global $handle;
        $fileList = [
            'file' => [],
            'directory' => []
        ];
        $maxData = [
            'children' => 2,
            'size' => 0
        ];
        $errorOutput = $output->getErrorOutput();
        foreach ($directories as $directory) {
            $fileInfo = $handle->getFileInfo($directory);
            /* 文件不存在 */
            if ($fileInfo === false) {
                $errorOutput->writeln("ls: cannot access '{$directory}': No such file or directory");
                continue;
            }
            
            /* 普通文件 */
            if (!$fileInfo['isDirectory']) {
                $fileList['file'] []= $fileInfo;
                $maxData['size'] = max($maxData['size'], $fileInfo['fileSize']);
                continue;
            }
            /* 文件夹 */
            $fileInfo['children'] = $handle->listDirectory($directory);
            $fileInfo['childrenCount'] = count($fileInfo['children']) + 2;
            $maxData['children'] = max($maxData['children'], $fileInfo['childrenCount']);
            /* 显示详细信息 */
            if ($option['long']) {
                foreach ($fileInfo['children'] as &$item) {
                    /* 统计子目录个数以及获取目录修改时间 */
                    if ($item['isDirectory']) {
                        $directoryName = $fileInfo['name'] . '/' . $item['name'];
                        $itemChildren = $handle->listDirectory($directoryName);
                        $item['childrenCount'] = count($itemChildren) + 2;
                        $maxData['children'] = max($maxData['children'], $item['childrenCount']);
                    } else {
                        $maxData['size'] = max($maxData['size'], $item['fileSize']);
                    }
                }
            }
            $fileList['directory'] []= $fileInfo;
        }
        return [
            'maxData' => $maxData,
            'fileList' => $fileList
        ];
    }
    
    private function printDirectory($list, $option, OutputInterface $output) {
        $maxData = $list['maxData'];
        $fileList = $list['fileList'];
        foreach ($fileList['file'] as $file) {
            $this->printFile($file, $option, $maxData, $output);
        }
        foreach ($fileList['directory'] as $postion => $directory) {
            if (count($fileList['file']) + count($fileList['directory']) !== 1) {
                if (count($fileList['file']) !== 0 || $postion !== 0) {
                    $output->writeln('');
                }
                $output->writeln("{$directory['name']}:");
            }
            foreach ($directory['children'] as $file) {
                $this->printFile($file, $option, $maxData, $output);
            }
        }
    }
    
    private function printFile($file, $option, $maxData, OutputInterface $output) {
        if ($option['long']) {
            $d = $file['isDirectory'] ? 'd' : '-';
            $x = $file['isDirectory'] ? 'x' : '-';
            $childrenLength = floor(log10($maxData['children']) + 1);
            $childrenCount = $file['isDirectory'] ? $file['childrenCount'] : 1;
            $sizeLength = floor(log10($maxData['size']) + 1);
            $fileSize = $file['fileSize'];
            $modifyTime = $file['modifyTime'];
            $time = (date('Y') === date('Y', $modifyTime) ? 
                    date('M d H:i', $modifyTime) : date('M d  Y', $modifyTime));
            
            $str = sprintf("%srw%srw%srw%s %{$childrenLength}d root root %{$sizeLength}d %s %s", 
                    $d, $x, $x, $x, $childrenCount, $fileSize, $time, $file['name']);
        } else {
            $str = $file['name'];
        }
        $output->writeln($str);
    }
}