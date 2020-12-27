<?php
include './utils/MyPDO.php';

class Controller
{
    protected $pdo;

    public function __construct()
    {
        $config = include './config/database.php';
        // 创建pdo对象
        $this->pdo = new MyPDO($config);
    }
}