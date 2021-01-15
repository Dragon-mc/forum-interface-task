<?php
include './core/Controller.php';

class MaincateController extends Controller
{
    // 获取主分类列表
    public function lists ($param) {
        $order = $param['sort'] == '+id' ? 'ASC' : 'DESC';
        $page = $param['page'];
        $limit = $param['limit'];
        $start = ($page - 1) * $limit;
        $where = !!$param['name'] ? "WHERE `name` LIKE '%{$param['name']}%'" : '';
        $total = $this->pdo->count('tb_main_cate');
        $sql = "SELECT main.id, main.name, main.time, main.admin_id, main.desc, main.status, ad.username as admin_name
                FROM `tb_main_cate` as main
                JOIN `tb_admin` as ad ON main.admin_id=ad.id
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

    // 添加主分类
    public function create ($param) {
        try {
            $this->pdo->insert('tb_main_cate', $param);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        $returnData = array('code'=> 20000);
        return json_encode($returnData);
    }

    // 更新主分类
    public function update ($param) {
        $id = $param['id'];
        try {
            $this->pdo->update('tb_main_cate', $param, "id={$id}", true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

    // 删除主分类
    public function delete ($param) {
        $id = $param['id'];
        try {
            $this->pdo->delete('tb_main_cate', "id={$id}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

    // 更新主分类状态
    public function updateStatus ($param) {
        $id = $param['id'];
        try {
            $this->pdo->update('tb_main_cate', $param, "id={$id}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

}