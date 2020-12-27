<?php
$uploadConfig = array(

    // 服务器域名或ip地址

    'domain' => 'http://www.forum.com',

    'product_path' => str_replace('\\', '/', realpath(dirname(dirname(__FILE__))))

);
//var_dump($uploadConfig);
return $uploadConfig;