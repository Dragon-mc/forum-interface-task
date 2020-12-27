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
            $total = $this->pdo->find("SELECT count(*) as NUM FROM `tb_post` as `post` WHERE `post`.title LIKE '%{$keywords}%' AND `post`.status=1")['NUM'];
            $res = $this->pdo->select("SELECT `post`.*, `user`.nickname, `user`.username, `user`.avatar FROM `tb_post` as `post`, `tb_user` as `user` WHERE `post`.title LIKE '%{$keywords}%' AND `post`.user_id=`user`.id AND `post`.status=1 LIMIT {$start}, {$limit}");
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