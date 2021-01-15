<?php
include './core/Controller.php';

class CommentController extends Controller
{
    // 获取评论列表
    public function lists ($param) {
        $order = $param['sort'] == '+id' ? 'ASC' : 'DESC';
        $page = $param['page'];
        $limit = $param['limit'];
        $start = ($page - 1) * $limit;
        $where = isset($param['title']) ? " WHERE post.title LIKE '%{$param['title']}%'" : '';
        $where .= isset($param['content']) ? (isset($param['title']) ? " AND com.content LIKE '%{$param['content']}%'" : "WHERE com.content LIKE '%{$param['content']}%'") : '';
        $total = $this->pdo->count('tb_comment');
        $sql = "SELECT com.*, user.username as user_name, post.title as post_name
                FROM `tb_comment` as `com`
                JOIN `tb_user` as `user` ON com.user_id=user.id
                JOIN `tb_post` as `post` ON com.post_id=post.id
                {$where}
                ORDER BY `id` {$order} LIMIT {$start}, {$limit}";
        // 获取语句异常，并返回错误的信息
        try {
            $data = $this->pdo->select($sql);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $sql));
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

    // 添加评论
    public function create ($param) {
        try {
            $this->pdo->insert('tb_post', $param);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        $returnData = array('code'=> 20000);
        return json_encode($returnData);
    }

    // 更新评论
    public function update ($param) {
        $id = $param['id'];
        try {
            $this->pdo->update('tb_post', $param, "id={$id}", true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

    // 删除评论
    public function delete ($param) {
        $id = $param['id'];
        try {
            $this->pdo->delete('tb_post', "id={$id}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

}