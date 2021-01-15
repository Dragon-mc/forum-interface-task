<?php
include './core/Controller.php';

class SearchController extends Controller
{
    // 获取指定关键字的帖子列表
    public function fetchSearchPost ($param) {
        $limit = $param['limit'];
        $page = $param['page'];
        $start = ($page - 1) * $limit;
        $keywords = $param['keywords'];
        try {
            $total = $this->pdo->count("tb_post", "title LIKE '%{$keywords}%' AND status=1");
            $sql = "SELECT `post`.*, `user`.nickname, `user`.username, `user`.avatar
                    FROM `tb_post` as `post`
                    JOIN `tb_user` as `user` ON `post`.user_id=`user`.id
                    WHERE `post`.title LIKE '%{$keywords}%' AND `post`.status=1
                    LIMIT {$start}, {$limit}";
            $res = $this->pdo->select($sql);
            foreach ($res as $key=>$val) {
                // 获取帖子被评论次数
                $res[$key]['comment_times'] = $this->pdo->count('tb_comment', "post_id={$val['id']}");
                // 获取帖子被浏览次数
                $res[$key]['read_times'] = $this->pdo->count('tb_history', "post_id={$val['id']}");
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000, 'data'=> array('total'=> (int)$total, 'items'=> $res)));
    }
}