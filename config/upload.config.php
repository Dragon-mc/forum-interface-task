<?php
$uploadConfig = array(

    // 服务器域名或ip地址

//    'domain' => 'http://www.forum.com',

    'domain' => 'http://wang1019.dev.dxdc.net/php2/forum-interface', // 也可使用外网地址

    // 项目文件绝对路径

    'product_path' => str_replace('\\', '/', realpath(dirname(dirname(__FILE__))))

);
// var_dump($uploadConfig);
return $uploadConfig;