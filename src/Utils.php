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
}