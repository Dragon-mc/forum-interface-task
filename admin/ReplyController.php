<?php
include './core/Controller.php';

class ReplyController extends Controller
{
    // 获取用户回复列表
    public function lists ($param) {
        $order = $param['sort'] == '+id' ? 'ASC' : 'DESC';
        $page = $param['page'];
        $limit = $param['limit'];
        $start = ($page - 1) * $limit;
        $where = !!$param['content'] ? "WHERE ur.content LIKE '%{$param['content']}%'" : '';
        $total = $this->pdo->count('tb_user_reply');
        $sql = "SELECT ur.*, `user`.username as user_name, `user1`.username as passive_user_name, com.content as comment_content
                FROM `tb_user_reply` as `ur`
                JOIN `tb_user` as `user` ON ur.user_id=user.id
                JOIN `tb_comment` as `com` ON ur.comment_id=com.id
                JOIN `tb_user` as `user1` ON ur.passive_user_id=`user`.id
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

    // 添加回复
    public function create ($param) {
        try {
            $this->pdo->insert('tb_user_reply', $param);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        $returnData = array('code'=> 20000);
        return json_encode($returnData);
    }

    // 编辑回复
    public function update ($param) {
        $id = $param['id'];
        try {
            $this->pdo->update('tb_user_reply', $param, "id={$id}", true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

    // 删除回复
    public function delete ($param) {
        $id = $param['id'];
        try {
            $this->pdo->delete('tb_user_reply', "id={$id}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

}