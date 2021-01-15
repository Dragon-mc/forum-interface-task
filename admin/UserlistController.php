<?php
include './core/Controller.php';

class UserlistController extends Controller
{
    // 获取用户列表
    public function lists ($param) {
        $order = $param['sort'] == '+id' ? 'ASC' : 'DESC';
        $page = $param['page'];
        $limit = $param['limit'];
        $start = ($page - 1) * $limit;
        $where = "WHERE `username` LIKE '%{$param['username']}%'";
        $where .= !!$param['nickname'] ? " AND nickname LIKE '%{$param['nickname']}%'" : '';
        $where .= !!$param['sex'] ? " AND sex='{$param['sex']}'" : '';
        $total = $this->pdo->count('tb_user');
        $sql = "SELECT id, username, nickname, sex, sign, introduction, create_time, last_time
                FROM `tb_user`
                {$where}
                ORDER BY `id` {$order} LIMIT {$start}, {$limit}";
        // 获取语句异常，并返回错误的信息
        try {
            $data = $this->pdo->select($sql);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        $returnData = array(
            'code'=> 20000,
            'data'=> array(
                'total'=> $total,
                'items'=> $data
            )
        );
        return json_encode($returnData);
    }

    // 添加用户
    public function create ($param) {
        try {
            $this->pdo->insert('tb_user', $param);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        $returnData = array('code'=> 20000);
        return json_encode($returnData);
    }

    // 编辑用户
    public function update ($param) {
        $id = $param['id'];
        try {
            $this->pdo->update('tb_user', $param, "id={$id}", true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

    // 删除用户
    public function delete ($param) {
        $id = $param['id'];
        try {
            $this->pdo->delete('tb_user', "id={$id}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

}