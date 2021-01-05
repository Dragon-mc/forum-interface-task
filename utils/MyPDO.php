<?php

class MyPDO
{
    protected $pdo;
    public $originPDO;

    public function __construct($config)
    {
        try {
            $dsn = "{$config['db_type']}:host={$config['db_host']};dbname={$config['db_name']};charset={$config['charset']}";
            if ($config['is_persistent']) {
                $this->pdo = new PDO($dsn, $config['db_user'], $config['db_pwd'], array(PDO::ATTR_PERSISTENT => true));
            } else {
                $this->pdo = new PDO($dsn, $config['db_user'], $config['db_pwd']);
            }
            $this->originPDO = $this->pdo;
        } catch (PDOException $e) {
            die ("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function select ($query)
    {
        $resSet = $this->pdo->query($query);
        $this->getPDOError();
        return $resSet->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find ($query)
    {
        $resSet = $this->pdo->query($query);
        $this->getPDOError();
        return $resSet->fetch(PDO::FETCH_ASSOC);
    }

    public function insert ($table, $arrayDataValue, $allowFields = false) {
        if ($allowFields)
            $this->fieldsFilter($table, $arrayDataValue);
//        $strSql = "INSERT INTO `$table` (`".implode('`,`', array_keys($arrayDataValue))."`) VALUES (':".implode("',':", array_keys($arrayDataValue))."')"; // implode("',':", $arrayDataValue)
        // $res = $this->pdo->query($strSql);
        $strSql = "INSERT INTO `$table` (`".implode('`,`', array_keys($arrayDataValue))."`) VALUES (:".implode(",:", array_keys($arrayDataValue)).")";
        $statement = $this->pdo->prepare($strSql);
        $values = array();
        foreach ($arrayDataValue as $key => $val) {
            $values[':'.$key] = $val;
        }
        $statement->execute($values);
        $this->getPDOError();
        return $statement->rowCount();
    }

    /**
     * Update 更新
     *
     * @param String $table 表名
     * @param Array $arrayDataValue 字段与值
     * @param String $where 条件
     * @return Int
     */
    public function update($table, $arrayDataValue, $where = '', $allowFields = false)
    {
        if ($allowFields)
            $this->fieldsFilter($table, $arrayDataValue);
        if ($where) {
            $strSql = '';
            foreach ($arrayDataValue as $key => $value) {
//                $strSql .= ", `$key`='$value'";
                $strSql .= ", `$key`=:$key";
            }
            $strSql = substr($strSql, 1);
            $strSql = "UPDATE `$table` SET $strSql WHERE $where";
        } else {
//            $strSql = "REPLACE INTO `$table` (`".implode('`,`', array_keys($arrayDataValue))."`) VALUES ('".implode("','", $arrayDataValue)."')";
            $strSql = "REPLACE INTO `$table` (`".implode('`,`', array_keys($arrayDataValue))."`) VALUES (:".implode(',:', array_keys($arrayDataValue)).")";
        }

//        $result = $this->pdo->query($strSql);
        $statement = $this->pdo->prepare($strSql);
        $values = array();
        foreach ($arrayDataValue as $key => $val) {
            $values[':'.$key] = $val;
        }
        $statement->execute($values);
        $this->getPDOError();
        return $statement->rowCount();
    }

    /**
     * Delete 删除
     *
     * @param String $table 表名
     * @param String $where 条件
     * @return Int
     */
    public function delete($table, $where = '')
    {
        if ($where == '') {
            $this->outputError("'WHERE' is Null");
        } else {
            $strSql = "DELETE FROM `$table` WHERE $where";
            $result = $this->pdo->query($strSql);
            $this->getPDOError();
            return $result->rowCount();
        }
    }

    public function count($table, $where = '') {
        if ($where != '') {
            $where = 'WHERE '. $where;
        }
        return (int)$this->select("SELECT count(*) as total FROM `$table` {$where}")[0]['total'];
    }

    /**
     * fieldsFilter 过滤掉数据表字段中不存在的数据
     *
     * @param String $table
     * @param array $arrayField
     */
    private function fieldsFilter($table, &$arrayFields)
    {
        $fields = $this->getFields($table);
        foreach ($arrayFields as $key => $value) {
            if (!in_array($key, $fields)) {
                unset($arrayFields[$key]);
            }
        }
    }

    /**
     * getFields 获取指定数据表中的全部字段名
     *
     * @param String $table 表名
     * @return array
     */
    private function getFields($table)
    {
        $fields = array();
        $recordset = $this->pdo->query("SHOW COLUMNS FROM $table");
        $this->getPDOError();
        $recordset->setFetchMode(PDO::FETCH_ASSOC);
        $result = $recordset->fetchAll();
        foreach ($result as $rows) {
            $fields[] = $rows['Field'];
        }
        return $fields;
    }

    /**
     * getPDOError 捕获PDO错误信息
     */
    private function getPDOError () {
        if ($this->pdo->errorCode() != '00000') {
            $arrayError = $this->pdo->errorInfo();
            $this->outputError($arrayError[2]);
        }
    }

    /**
     * 输出错误信息
     *
     * @param String $strErrMsg
     */
    private function outputError($strErrMsg)
    {
        throw new Exception('Error: '.$strErrMsg);
    }

    public function destruct()
    {
        $this->pdo = null;
    }
}