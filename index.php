<?php
// 入口文件index.php

//header("Access-Control-Allow-Origin:".(isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] :  get_client_ip()));
//解决跨域
header("Access-Control-Allow-Origin: *");

// 加载路由
require './router/route.php';