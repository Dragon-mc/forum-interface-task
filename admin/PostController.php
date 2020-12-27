<?php
include './core/Controller.php';

class PostController extends Controller
{
    public function lists ($param) {
        $order = $param['sort'] == '+id' ? 'ASC' : 'DESC';
        $page = $param['page'];
        $limit = $param['limit'];
        $start = ($page - 1) * $limit;
        $where = !!$param['title'] ? "AND post.title LIKE '%{$param['title']}%'" : '';
        $where .= !!$param['sub_id'] ? " AND post.sub_id={$param['sub_id']}" : '';
        $where .= !!$param['content'] ? " AND post.content LIKE '%{$param['content']}%'" : '';
        $total = $this->pdo->count('tb_post');
        $sql = "SELECT post.*, user.username as user_name, sub.name as sub_name FROM `tb_post` as `post`, `tb_sub_cate` as `sub`, `tb_user` as `user` WHERE post.user_id=user.id AND post.sub_id=sub.id {$where} ORDER BY `id` {$order} LIMIT {$start}, {$limit}";
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

    public function subCate () {
        try {
            $mainCate = $this->pdo->select("SELECT `id`, `name` FROM `tb_sub_cate`");
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
            $this->pdo->insert('tb_post', $param);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        $returnData = array('code'=> 20000);
        return json_encode($returnData);
    }

    public function update ($param) {
        $id = $param['id'];
        try {
            $this->pdo->update('tb_post', $param, "id={$id}", true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

    public function delete ($param) {
        $id = $param['id'];
        try {
            $this->pdo->delete('tb_post', "id={$id}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

    public function updateStatus ($param) {
        $id = $param['id'];
        try {
            $this->pdo->update('tb_post', $param, "id={$id}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

}