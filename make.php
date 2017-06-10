#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
    error("Run for command line only.");
}
 
if (Phar::canWrite() === false) {
    error("Phar can not write, Set \"phar.readonly = Off\" in php.ini.");
}

$pharName = dirname(__DIR__) . '/coscmd.phar';
if (file_exists($pharName)) {
    unlink($pharName);
}

$phar = new Phar($pharName);
$phar->startBuffering();
$phar->buildFromDirectory(__DIR__);
$phar->compressFiles(Phar::GZ);
$stub = "#!/usr/bin/env php\n";
$stub .= $phar->createDefaultStub('app.php');
$phar->setStub($stub);
$phar->stopBuffering();

$source = __DIR__ . '/config.php';
$destnation = dirname(__DIR__) . '/config.php';
if (!file_exists($destnation)) {
    copy(__DIR__ . '/config.php', dirname(__DIR__));
}

chmod($pharName, 0755);
