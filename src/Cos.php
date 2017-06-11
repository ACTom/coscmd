<?php

namespace ACTom\COSCmd;

use QCloud\Cos\Api;

class Cos {
    private $api = null;
    private $bucket = '';
    private $errorNo = 0;
    private $errorMsg = '';
    
    public function __construct($conf = null) {
        if ($conf === null) {
            global $config;
            $conf = $config;
        }
        $this->bucket = $conf['bucket'];
        $this->api = new Api($conf);
    }
    
    private function returnResult($result) {
        if (!is_array($result)) {
            $this->errorNo = -1;
            $this->errorMsg = 'Return Error';
            return false;
        }
        if ($result['code'] === 0) {
            return true;
        } else {
            $this->errorNo = $result['code'];
            $this->errorMsg = $result['message'];
            return false;
        }
    }
    
    public function listBucket() {
        $result = $this->listDirectory('/', false, '');
        return $result;
    }
    
    public function setBucket($bucket) {
        $bucketList = $this->listBucket();
        foreach ($bucketList as $aBucket) {
            if ($bucket === $aBucket['name']) {
                $this->bucket = $bucket;
                return true;
            }
        }
        $this->errorNo = -133;
        $this->errorMsg = 'bucket does not exists.';
        return false;
    }
    
    public function getBucket() {
        return $this->bucket;
    }
    
    public function createDirectory($directoryPath) {
        $result = $this->api->createFolder($this->bucket, $directoryPath);
        return $this->returnResult($result);
    }
    
    public function uploadFile($filePath, $destPath) {
        $result = $this->api->upload($this->bucket, $filePath, $destPath);
        return $this->returnResult($result);
    }
    
    public function downloadFile($filePath, $destPath) {
        $result = $this->api->download($this->bucket, $filePath, $destPath);
        return $this->returnResult($result);
    }
    
    public function listDirectory($directoryPath, $showAll = false, $bucket = null) {
        if ($bucket === null) {
            $bucket = $this->bucket;
        }
        $directoryPath = Utils::normalizerRemotePath($directoryPath);
        $result = $this->api->listFolder($bucket, $directoryPath);
        if ($result['code'] !== 0) {
            return $this->returnResult($result);
        }
        $list = [];
        if ($showAll) {
            $thisDirectory = $this->getFileInfo($directoryPath);
            $thisDirectory['name'] = '.';
            $parentDirectory = $this->getFileInfo(Utils::normalizerRemotePath($directoryPath . '/..'));
            $parentDirectory['name'] = '..';
            $list []= $thisDirectory;
            $list []= $parentDirectory;
        }
        foreach ($result['data']['infos'] as $item) {
            $name = $item['name'];
            if (!$showAll && $name{0} === '.') {
                continue;
            }
            $isDirectory = substr($name, -1 ,1) === '/';
            if ($isDirectory) {
                $name = substr($name, 0, strlen($name) - 1);
            }
            $fileInfo = [
                'name' => $name,
                'isDirectory' => $isDirectory,
                'fileSize' => $isDirectory ? 0 : $item['filesize'],
                'createTime' => $item['ctime'],
                'modifyTime' => $item['mtime']
            ];
            $list []= $fileInfo;
        }
        return $list;
    }
    
    public function updateDirectory($directoryPath, $bizAttr) {
        $result = $this->api->updateFolder($this->bucket, $directoryPath, $bizAttr);
        return $this->returnResult($result);
    }
    
    public function updateFile($filePath, $bizAttr, $authority, $customerHeaders) {
        $result = $this->api->update($this->bucket, $filePath, $bizAttr, $authority, $customerHeaders);
        return $this->returnResult($result);
    }
    
    public function getFileInfo($filePath) {
        $result = $this->api->stat($this->bucket, $filePath);
        /* 如果获取文件失败 */
        if ($result['code'] !== 0 || $filePath === '/') {
            $resultDirectory = $this->api->statFolder($this->bucket, $filePath);
            if ($resultDirectory['code'] !== 0) {
                return $this->returnResult($result);
            }
            $isDirectory = 1;
            $data = $resultDirectory['data'];
        } else {
            $isDirectory = 0;
            $data = $result['data'];
        }
        $fileInfo = [
            'name' => basename($filePath),
            'fullPath' => $filePath,
            'isDirectory' => $isDirectory,
            'fileSize' => $isDirectory ? 0 : $data['filesize'],
            'createTime' => $data['ctime'],
            'modifyTime' => $data['mtime']
        ];
        return $fileInfo;
    }
    
    public function fileExists($filePath) {
        $fileInfo = $this->getFileInfo($filePath);
        return $fileInfo ? true : false;
    }
    
    public function isDirectory($filePath) {
        $fileInfo = $this->getFileInfo($filePath);
        return ($fileInfo && $fileInfo['isDirectory']) ? true : false;
    }
    
    public function copyFile($source, $destnation) {
        $result = $this->api->copyFile($this->bucket, $source, $destnation);
        return $this->returnResult($result);
    }
    
    public function moveFile($source, $destnation) {
        $result = $this->api->moveFile($this->bucket, $source, $destnation);
        return $this->returnResult($result);
    }
    
    public function deleteFile($filePath) {
        $result = $this->api->delFile($this->bucket, $filePath);
        return $this->returnResult($result);
    }
    
    public function deleteDirectory($directoryPath) {
        $result = $this->api->delFolder($this->bucket, $directoryPath);
        return $this->returnResult($result);
    }
    
    public function getLastErrorNo() {
        return $this->errorNo;
    }
    
    public function getLastError() {
        return $this->errorMsg;
    }
}