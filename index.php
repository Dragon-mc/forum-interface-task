<?php
// 入口文件index.php

//解决跨域
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
header("Access-Control-Request-Headers: *");

// 加载路由
require './router/route.php';