<?php

namespace ACTom\COSCmd;

class Utils {
    
    public static function normalizerRemotePath($path) {
        if (preg_match('/^\//', $path) === 0) {
            $path = '/' . $path;
        }
        
        $path = preg_replace('#/+#', '/', $path);
        return $path;
    }
    
    public static function checkRequirement() {
        if (php_sapi_name() !== 'cli') {
            exit('This program can only run in CLI mode');
        }
        
        if (version_compare(PHP_VERSION, '5.5.9', '<')) {
            exit('Need PHP Version >= 5.5.9, Your version is ' . PHP_VERSION . "\n");
        }
    }
}