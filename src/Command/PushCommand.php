<?php

namespace ACTom\COSCmd\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;
use ACTom\COSCmd\Utils;


class PushCommand extends BaseCommand {
    private $options = [];

    protected function configure() {
        $this
            ->setName('push')
            ->setDescription('Upload file to cos')
            ->setHelp('This command allows you to upload file to cos')
            ->addArgument('source', InputArgument::REQUIRED, 'Which file would you like to upload?')
            ->addArgument('destnation', InputArgument::REQUIRED, 'Where would you like to upload to?')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'If file exists, overwrite it.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $source = $input->getArgument('source');
        $destnation = $input->getArgument('destnation');
        $this->doAction($source, $destnation, $input, $output);
    }
    
    public function doAction($source, $destnation, InputInterface $input, OutputInterface $output, $baseDirectory = '/') {
        $handle = $this->getHandle();
        $source = Utils::normalizerLocalPath($source);
        $destnation = Utils::normalizerRemotePath($destnation, $baseDirectory);
        $errorOutput = $output->getErrorOutput();
        $this->options = $input->getOptions();
        if ($this->options['force'] && $this->options['no-interaction']) {
            $errorOutput->writeln("push: --force and --no-interaction options cannot used at the same time.");
            return ;
        }
        if (!file_exists($source)) {
            $errorOutput->writeln("push: {$source} does not exists.");
            return ;
        }
        if ($handle->isDirectory($destnation)) {
            $basename = basename($source);
            $destnation = $destnation . '/' . $basename;
        }
        if (is_dir($source)) {
            if ($handle->fileExists($destnation) && !$handle->isDirectory($destnation)) {
                $errorOutput->writeln("push: cannot overwrite non-directory {$destnation} with directory {$source}.");
                return ;
            }
            $this->uploadDirectory($source, $destnation, $input, $output);
        } else {
            $this->uploadFile($source, $destnation, $input, $output);
        }
    }
    
    private function uploadDirectory($source, $destnation, InputInterface $input, OutputInterface $output) {
        $handle = $this->getHandle();
        $target = $source = [[
            'source' => $source,
            'destnation' => $destnation,
            'isDirectory' => true
        ]];
        while (($item = array_pop($source))) {
            $dirObject = dir($item['source']);
            while (($file = $dirObject->read())) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $fileInfo = [
                    'source' => $item['source'] . DIRECTORY_SEPARATOR . $file,
                    'destnation' => Utils::normalizerRemotePath($item['destnation'] . '/' . $file)
                ];
                $fileInfo['isDirectory'] = is_dir($fileInfo['source']);
                if ($fileInfo['isDirectory']) {
                    array_push($source, $fileInfo);
                }
                array_push($target, $fileInfo);
        	}
            $dirObject->close();
        }
        $count = 0;
        $maxCount = count($target);
        foreach ($target as $item) {
            $count ++;
            if ($item['isDirectory']) {
                if ($this->options['verbose']) {
                    $output->writeln("[{$count}/{$maxCount}] Creating Directory: {$item['destnation']}");
                }
                $handle->createDirectory($item['destnation']);
            } else {
                if ($this->options['verbose']) {
                    $output->writeln("[{$count}/{$maxCount}] Upload File: {$item['source']} to {$item['destnation']}");
                }
                $this->uploadFile($item['source'], $item['destnation'], $input, $output);
            }
        }
    }
    
    private function uploadFile($source, $destnation, InputInterface $input, OutputInterface $output) {
        $handle = $this->getHandle();
        $errorOutput = $output->getErrorOutput();
        if ($handle->fileExists($destnation)) {
            if ($this->options['no-interaction']) {
                return true;
            }
            if (!$this->options['force']) {
                $question = new ConfirmationQuestion("push: overwrite {$destnation} ? [Y/n]", true);
                $result = (new QuestionHelper())->doAsk($output, $question);
                if (!$result) {
                    return true;
                }
            }
        }
        if (!$handle->uploadFile($source, $destnation)) {
            $errorNo = $handle->getLastErrorNo();
            $errorMsg = $handle->getLastError();
            $output->writeln("push: upload file {$source} to {$destnation}: fail, error code:{$errorNo}, error message: {$errorMsg}");
            return false;
        }
        return true;
    }
}