<?php
include './core/Controller.php';

class FeedbackController extends Controller
{
    public function lists ($param) {
        $order = $param['sort'] == '+id' ? 'ASC' : 'DESC';
        $page = $param['page'];
        $limit = $param['limit'];
        $start = ($page - 1) * $limit;
        $where = !!$param['content'] ? " AND fb.content LIKE '%{$param['content']}%'" : '';
        $has_reply = $param['has_reply'];
        $sql = "SELECT fb.*, user.username FROM `tb_feedback` as fb, `tb_user` as `user` WHERE fb.user_id=user.id {$where} ORDER BY `id` {$order} LIMIT {$start}, {$limit}";
        // 获取语句异常，并返回错误的信息
        try {
            $data = $this->pdo->select($sql);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        // 遍历查询结果，查找出反馈的所有回复
        foreach ($data as $key=>$val) {
            try {
                $data[$key]['reply_data'] = $this->pdo->select("SELECT ar.*, admin.username as admin_name FROM `tb_admin_reply` as ar, `tb_admin` as admin WHERE feedback_id={$val['id']} AND ar.admin_id=admin.id");
            } catch (Exception $e) {
                return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
            }
            $data[$key]['reply_times'] = sizeof($data[$key]['reply_data']);
        }
        // 如果存在回复条件筛选，则遍历将不满足条件的内容删除
        if (!!$has_reply)
            for ($i = sizeof($data)-1; $i >= 0; $i--) {
                if ($has_reply=='已回复') {
                    if ($data[$i]['reply_times'] <= 0) {
                        array_splice($data, $i, 1);
                    }
                } else if ($has_reply=='未回复') {
                    if ($data[$i]['reply_times'] > 0) {
                        array_splice($data, $i, 1);
                    }
                }
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

    public function replyUser ($param) {
        try {
            $this->pdo->insert('tb_admin_reply', $param);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        $returnData = array('code'=> 20000);
        return json_encode($returnData);
    }

    public function delete ($param) {
        $id = $param['id'];
        try {
            $this->pdo->delete('tb_feedback', "id={$id}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

}