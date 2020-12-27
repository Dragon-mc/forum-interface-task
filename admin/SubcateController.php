<?php
include './core/Controller.php';

class SubcateController extends Controller
{
    public function lists ($param) {
        $order = $param['sort'] == '+id' ? 'ASC' : 'DESC';
        $page = $param['page'];
        $limit = $param['limit'];
        $start = ($page - 1) * $limit;
        $where = !!$param['name'] ? "AND sub.name LIKE '%{$param['name']}%'" : '';
        $total = $this->pdo->count('tb_main_cate');
        $sql = "SELECT sub.id, sub.main_id, sub.name, sub.time, sub.desc, sub.status, sub.admin_id, main.name as main_name, ad.username as admin_name FROM `tb_main_cate` as main, `tb_sub_cate` as sub, `tb_admin` as ad WHERE sub.main_id=main.id AND sub.admin_id=sub.admin_id {$where} ORDER BY `id` {$order} LIMIT {$start}, {$limit}";
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

    public function mainCate () {
        try {
            $mainCate = $this->pdo->select("SELECT `id`, `name` FROM `tb_main_cate`");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array(
            'code'=> 20000,
            'data'=> $mainCate
        ));
    }

    public function create ($param) {
        try {
            $this->pdo->insert('tb_sub_cate', $param);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        $returnData = array('code'=> 20000);
        return json_encode($returnData);
    }

    public function update ($param) {
        $id = $param['id'];
        try {
            $this->pdo->update('tb_sub_cate', $param, "id={$id}", true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

    public function delete ($param) {
        $id = $param['id'];
        try {
            $this->pdo->delete('tb_sub_cate', "id={$id}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

    public function updateStatus ($param) {
        $id = $param['id'];
        try {
            $this->pdo->update('tb_sub_cate', $param, "id={$id}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

}