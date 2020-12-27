<?php

// 1.解析出PATHINFO
$pathInfo = array_values(array_filter(explode('/',$_SERVER['PATH_INFO'])));

// 2.解析模块
$module = array_shift($pathInfo);

// 3.解析控制器
$controller = ucfirst(strtolower(array_shift($pathInfo))) . 'Controller';

// 4.解析控制器中的方法
$action = strtolower(array_shift($pathInfo));

// 5.解析参数
$params = array();
//parse_str($_SERVER['QUERY_STRING'], $params);
//return json_encode(array('code'=> 20000, 'data'=> $_FILES));
if (!empty($_FILES)) {
    $params = $_FILES;
}
else if (empty($_GET)) {
    $params = json_decode(file_get_contents('php://input'), true);
    if (empty($params)) {
        $params = json_decode(array_keys($_POST)[0], true);
        if (empty($params)) {
            $params = $_POST;
        }
    }
} else {
    $params = $_GET;
}

// 6.调用控制器办法
if (file_exists($module.'/'.$controller.'.php')) {
    include $module.'/'.$controller.'.php';
} else {
    exit('controller is not define');
}

if (method_exists($controller, $action)) {
    $user = new $controller;
    echo $user->$action($params);
} else {
    exit('action is not define');
}