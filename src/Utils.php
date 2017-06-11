<?php

namespace ACTom\COSCmd;

use Phar;

class Utils {
    /* 格式化远程目录 
     * @param $path          string 待格式化目录
     * @param $baseDirectory string 基础目录
     * @return               string 格式化后目录
     */
    public static function normalizerRemotePath($path, $baseDirectory = null) {
        if ($baseDirectory !== null) {
            $path = $baseDirectory . '/' . $path;
        }
        /* 删除重复的/ */
        $path = preg_replace('#/+#', '/', $path);
        
        /* 删除./ ../ */
        $components=[];
        foreach(explode('/', $path) as $name) {
            if ($name === '..') {
                array_pop($components);
            } elseif ($name === '.' || $name === '') {
                continue;
            } else {
                $components[]=$name;
            }
        }
        $path = '/' . implode('/', $components);
        return $path;
    }
    
    public static function localPath($path) {
        if (!$path) {
            return $path;
        }
        if (Phar::running(false) && !file_exists($path) && $path{0} !== '/') {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }
        return $path;
    }
    
    /*
     * 检查程序运行环境
     */
    public static function checkRequirement() {
        if (php_sapi_name() !== 'cli') {
            exit('This program can only run in CLI mode');
        }
        
        if (version_compare(PHP_VERSION, '5.5.9', '<')) {
            exit('Need PHP Version >= 5.5.9, Your version is ' . PHP_VERSION . "\n");
        }
    }
}