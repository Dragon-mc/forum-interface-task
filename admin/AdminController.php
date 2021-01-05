<?php
include './core/Controller.php';

class AdminController extends Controller
{
    // 获取管理员列表
    public function lists ($param) {
        $order = $param['sort'] == '+id' ? 'ASC' : 'DESC';
        $page = $param['page'];
        $limit = $param['limit'];
        $start = ($page - 1) * $limit;
        $where = "WHERE `username` LIKE '%{$param['username']}%'";
        $where .= ($param['level']==='0' || $param['level']==='1') ? " AND `level`={$param['level']}" : '';
        $sql = "SELECT id, username, introduction, create_time, `level` FROM `tb_admin` {$where} ORDER BY `id` {$order} LIMIT {$start}, {$limit}";
        // 获取语句异常，并返回错误的信息
        try {
            $data = $this->pdo->select($sql);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        $total = sizeof($data);
        $returnData = array(
            'code'=> 20000,
            'data'=> array(
                'total'=> $total,
                'items'=> $data
            )
        );
        return json_encode($returnData);
    }

    // 添加管理员
    public function create ($param) {
        $param['password'] = md5($param['password']);
        try {
            $this->pdo->insert('tb_admin', $param);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        $returnData = array('code'=> 20000);
        return json_encode($returnData);
    }

    // 编辑管理员
    public function update ($param) {
        $id = $param['id'];
        try {
            $this->pdo->update('tb_admin', $param, "id={$id}", true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

    // 删除管理员
    public function delete ($param) {
        $id = $param['id'];
        try {
            $this->pdo->delete('tb_admin', "id={$id}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

}