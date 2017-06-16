# QCloud COS Cmd Tool
腾讯云COS命令行操作工具

## 运行环境
- PHP： 5.4.0

## 配置
配置文件按照如下顺序读取：-c参数设置, ~/.coscmd.conf, /etc/coscmd.conf, 程序目录config.php

配置文件格式：
```php
<?php

return [
    'app_id' => '',
    'secret_id' => '',
    'secret_key' => '',
    'bucket' => '',
    'region' => 'gz', // 华南->gz, 华中->sh, 华北->tj
    'timeout' => 60
];
```

## 支持的命令
命令支持参数请使用-h获取

    cp      在cos上复制文件/文件夹(源、目标均在cos)
    ls      列出cos上的文件/文件夹
    mkdir   在cos上创建文件夹
    mv      在cos上移动文件/文件夹(源、目标均在cos)
    pull    从cos下载文件/文件夹
    push    从本地上传文件/文件夹至cos
    rm      删除cos上文件/文件夹
    rmdir   删除cos上空文件夹


## 使用示例

    ./coscmd.phar 
    ./coscmd.phar ls /
    ./coscmd.phar ls -al /

    ./coscmd.phar cp /a /b
    ./coscmd.phar cp -rv /c /d

    ./coscmd.phar push folder /
    ./coscmd.phar pull /folder .

    ./coscmd.phar rm -rv /d
    ./coscmd.phar rm /b

