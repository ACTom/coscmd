<?php

namespace ACTom\COSCmd;

use Phar;

class Utils {
    /* 格式化远程目录 
     * @param $path          string 待格式化目录
     * @param $baseDirectory string 基础目录
     * @return               string 格式化后目录
     */
    public static function normalizerRemotePath($path, $baseDirectory = '/') {
        return self::normalizerLinux($path, $baseDirectory);
    }
    
    public function normalizerLocalPath($path, $baseDirectory = null) {
        return DIRECTORY_SEPARATOR === '/' ? self::normalizerLinux($path, $baseDirectory) : self::normalizerWindows($path, $baseDirectory);
    }
    
    private function normalizerLinux($path, $baseDirectory = null) {
        if ($baseDirectory === null) {
            $baseDirectory = getcwd();
        }
        if (strlen($path) > 0 && $path{0} !== '/') {
            $path = $baseDirectory . '/' . $path;
        }
        if (strlen($path) > 0 && $path[0] !== '/') {
            $path = getcwd() . '/' . $path;
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
    
    private function normalizerWindows($path, $baseDirectory = null) {
        if ($baseDirectory === null) {
            $baseDirectory = getcwd();
        }
        $pathArr = self::splitPathWindows($path);
        if (!$pathArr['letter']) {
            $baseArr = self::splitPathWindows($baseDirectory);
            $currentArr = self::splitPathWindows(getcwd());
            $pathArr['letter'] = $baseArr['letter'] ? $baseArr['letter'] : $currentArr['letter'];
            if (strlen($pathArr['path'])>0 && $pathArr['path']{0} === '\\') {
                
            } elseif ($baseArr['letter'] || (strlen($baseArr['path']) > 1 && $baseArr['path']{0} === '\\')) {
                $pathArr['path'] = $baseArr['path'] . '\\' . $pathArr['path'];
            } else {
                $pathArr['path'] = $currentArr['path'] . '\\' . $baseArr['path'] . '\\' . $pathArr['path'];
            }
        }
        $path = $pathArr['path'];
        
        /* 删除重复的/ */
        $path = preg_replace('#\\+#', '\\', $path);
        
        /* 删除./ ../ */
        $components=[];
        foreach(explode('\\', $path) as $name) {
            if ($name === '..') {
                array_pop($components);
            } elseif ($name === '.' || $name === '') {
                continue;
            } else {
                $components[]=$name;
            }
        }
        $path = $pathArr['letter'] . '\\' . implode('\\', $components);
        return $path;
    }
    
    private static function splitPathWindows($path) {
        if (strlen($path) > 1 && $path{1} === ':') {
            return [
                'letter' => substr($path, 0, 2),
                'path' => substr($path, 2)
            ];
        } else {
            return [
                'letter' => '',
                'path' => $path
            ];
        }
    }
    
    /*
     * 检查程序运行环境
     */
    public static function checkRequirement() {
        if (php_sapi_name() !== 'cli') {
            exit('This program can only run in CLI mode');
        }
        
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            exit('Need PHP Version >= 5.4.0, Your version is ' . PHP_VERSION . "\n");
        }
    }
}