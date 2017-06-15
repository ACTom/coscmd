<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use ACTom\COSCmd\Utils;


class LsCommand extends BaseCommand {
    
    private $options = [];

    protected function configure() {
        $this
            ->setName('ls')
            ->setAliases(['dir'])
            ->setDescription('List directory contents')
            ->setHelp('Displays the infomation of files within the directory')
            ->addArgument('file', InputArgument::IS_ARRAY, 
                'Which file would you like to display?', ['/'])
            ->addOption('long', 'l', InputOption::VALUE_NONE, 'use a long listing format')
            ->addOption('color', null, InputOption::VALUE_OPTIONAL, "colorize the output; WHEN can be 'never' or 'auto'(the default); more info below", 'auto')
            ->addOption('classify', 'F', InputOption::VALUE_NONE, 'append indicator (one of */=>@|) to entries')
            ->addOption('directory', 'd', InputOption::VALUE_NONE, 'list directories themselves, not their contents')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'do not ignore entries starting with .')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $files = $input->getArgument('file');
        $this->doAction($files, $input, $output);
    }
    
    public function doAction($files, InputInterface $input, OutputInterface $output, $baseDirectory = '/') {
        $this->options = $input->getOptions();
        $list = $this->getFileList($files, $output, $baseDirectory);
        $this->printDirectory($list, $output);
    }
    
    private function getFileList($files, OutputInterface $output, $baseDirectory) {
        $options = $this->options;
        $handle = $this->getHandle();
        $fileList = [
            'file' => [],
            'directory' => []
        ];
        $maxData = [
            'children' => 2,
            'size' => 0
        ];
        $errorOutput = $output->getErrorOutput();
        foreach ($files as $filePath) {
            $fullPath = Utils::normalizerRemotePath($filePath, $baseDirectory);
            $fileInfo = $handle->getFileInfo($fullPath);
            /* 文件不存在 */
            if ($fileInfo === false) {
                $errorOutput->writeln("<error>ls: cannot access '{$filePath}': No such file or directory</>");
                continue;
            }
            
            /* 普通文件 */
            if (!$fileInfo['isDirectory']) {
                $fileList['file'] []= $fileInfo;
                $maxData['size'] = max($maxData['size'], $fileInfo['fileSize']);
                continue;
            }
            /* 文件夹 */
            $fileInfo['children'] = $handle->listDirectory($fullPath, $options['all']);
            $fileInfo['childrenCount'] = count($fileInfo['children']) + 2;
            $maxData['children'] = max($maxData['children'], $fileInfo['childrenCount']);
            if (!$options['directory']) {
                /* 显示详细信息 */
                if ($options['long']) {
                    foreach ($fileInfo['children'] as &$item) {
                        /* 统计子目录个数以及获取目录修改时间 */
                        if ($item['isDirectory']) {
                            $directoryName = $fileInfo['fullPath'] . '/' . $item['name'];
                            $itemChildren = $handle->listDirectory($directoryName);
                            $item['childrenCount'] = count($itemChildren) + 2;
                            $maxData['children'] = max($maxData['children'], $item['childrenCount']);
                        } else {
                            $maxData['size'] = max($maxData['size'], $item['fileSize']);
                        }
                    }
                }
                $fileList['directory'] []= $fileInfo;
            } else {
                $fileList['file'] [] = $fileInfo;
            }
        }
        return [
            'maxData' => $maxData,
            'fileList' => $fileList
        ];
    }
    
    private function printDirectory($list, OutputInterface $output) {
        $options = $this->options;
        $maxData = $list['maxData'];
        $fileList = $list['fileList'];
        foreach ($fileList['file'] as $file) {
            $this->printFile($file, $maxData, $output);
        }
        foreach ($fileList['directory'] as $postion => $directory) {
            if (count($fileList['file']) + count($fileList['directory']) !== 1) {
                if (count($fileList['file']) !== 0 || $postion !== 0) {
                    $output->writeln('');
                }
                if ($directory['name'] === '') {
                    $directory['name'] = '/';
                }
                $output->writeln("{$directory['name']}:");
            }
            foreach ($directory['children'] as $file) {
                $this->printFile($file, $maxData, $output);
            }
        }
    }
    
    private function printFile($file, $maxData, OutputInterface $output) {
        $options = $this->options;
        $name = $this->renderFilename($file);
        if ($options['long']) {
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
                    $d, $x, $x, $x, $childrenCount, $fileSize, $time, $name);
        } else {
            $str = $name;
        }
        $output->writeln($str);
    }
    
    private function renderFilename($file) {
        $options = $this->options;
        $result = $file['name'];
        if ($options['color'] === 'auto') {
            if ($file['isDirectory']) {
                $result = "<fg=blue>{$file['name']}</>";
            }
        }
        if ($options['classify'] && $file['isDirectory']) {
            $result .= '/';
        }
        return $result;
    }
}