<?php
include './core/Controller.php';

class IndexController extends Controller
{
    public function fetchRecommendPostList ($param) {
        $limit = $param['limit'];
        $skip = $param['skip'];
        try {
            $res = $this->pdo->select("SELECT `post`.*, `user`.nickname, `user`.username, `user`.avatar FROM `tb_post` as `post`, `tb_user` as `user` WHERE `post`.user_id=`user`.id AND `post`.status=1 LIMIT {$skip}, {$limit}");
            foreach ($res as $key=>$val) {
                // 获取帖子被评论次数
                $res[$key]['comment_times'] = $this->pdo->count('tb_comment', "post_id={$val['id']}");
                // 获取帖子被浏览次数
                $res[$key]['read_times'] = $this->pdo->count('tb_history', "post_id={$val['id']}");
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000, 'data'=> $res));
    }

    public function fetchRankList () {
        $res = array();
        try {
            $browse_desc = $this->pdo->select("SELECT post_id, count(id) as `read_times` FROM `tb_history` GROUP BY post_id ORDER BY `read_times` DESC LIMIT 5");
            $browse_rank = array();
            foreach ($browse_desc as $key=>$val) {
                // 获取每条浏览排行中 帖子信息 和 用户头像
                $browse_rank[$key] = $this->pdo->find("SELECT `post`.title, `user`.avatar, `post`.id FROM `tb_post` as `post`, `tb_user` as `user` WHERE `post`.id={$val['post_id']} AND `post`.user_id=`user`.id");
            }
            $comment_rank = $this->pdo->select("SELECT post_id, count(id) as `commented_times` FROM `tb_comment` GROUP BY post_id ORDER BY `commented_times` DESC LIMIT 5");
            foreach ($comment_rank as $key=>$val) {
               // 获取每个帖子的详情 和 用户信息
                $comment_rank[$key]['details'] = $this->pdo->find("SELECT `user`.nickname, `user`.username, `user`.avatar, `user`.sign, `post`.title FROM `tb_post` as `post`, `tb_user` as `user` WHERE `post`.id={$val['post_id']} AND `post`.user_id=`user`.id");
            }
            $attention_rank = $this->pdo->select("SELECT passive_id, count(*) as `passive_attention_num` FROM `tb_user_relation` GROUP BY passive_id ORDER BY `passive_attention_num` DESC LIMIT 5");
            foreach ($attention_rank as $key=>$val) {
                $attention_rank[$key]['user_info'] = $this->pdo->find("SELECT nickname, username, avatar FROM `tb_user` WHERE id={$val['passive_id']}");
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        $res['browse_rank'] = $browse_rank;
        $res['comment_rank'] = $comment_rank;
        $res['attention_rank'] = $attention_rank;
        return json_encode(array('code'=> 20000, 'data'=> $res));
    }

    public function fetchCateList () {
        try {
            $res = $this->pdo->select("SELECT id, name FROM `tb_main_cate`");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> $res));
    }

    public function fetchBrowseRank ($param) {
        $limit = $param['limit'];
        $skip = $param['skip'];
        try {
            $browse_desc = $this->pdo->select("SELECT post_id, count(id) as `read_times` FROM `tb_history` GROUP BY post_id ORDER BY `read_times` DESC LIMIT {$skip}, {$limit}");
            foreach ($browse_desc as $key=>$val) {
                // 获取每条浏览排行中 帖子信息 和 用户头像
                $browse_desc[$key] = array_merge($browse_desc[$key], $this->pdo->find("SELECT `post`.title, `user`.avatar, `user`.nickname, `user`.username, `user`.id as `user_id` FROM `tb_post` as `post`, `tb_user` as `user` WHERE `post`.id={$val['post_id']} AND `post`.user_id=`user`.id"));
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> $browse_desc));
    }

    public function fetchCommentRank ($param) {
        $limit = $param['limit'];
        $skip = $param['skip'];
        try {
            $comment_rank = $this->pdo->select("SELECT post_id, count(id) as `commented_times` FROM `tb_comment` GROUP BY post_id ORDER BY `commented_times` DESC LIMIT {$skip}, {$limit}");
            foreach ($comment_rank as $key=>$val) {
                // 获取每条帖子的浏览次数
                $comment_rank[$key]['read_times'] = $this->pdo->count('tb_history', "post_id={$val['post_id']}");
                // 获取每个帖子的详情 和 用户信息
                $comment_rank[$key] = array_merge($comment_rank[$key], $this->pdo->find("SELECT `user`.nickname, `user`.username, `user`.avatar, `user`.id as `user_id`, `post`.title, `post`.content, `post`.time FROM `tb_post` as `post`, `tb_user` as `user` WHERE `post`.id={$val['post_id']} AND `post`.user_id=`user`.id"));
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000, 'data'=> $comment_rank));
    }

    public function fetchAttentionRank ($param) {
        $visit_id = $param['visit_id'];
        $limit = $param['limit'];
        $skip = $param['skip'];
        try {
            $attention_rank = $this->pdo->select("SELECT passive_id, count(id) as `fans_num` FROM `tb_user_relation` GROUP BY passive_id ORDER BY `fans_num` DESC LIMIT {$skip}, {$limit}");
            foreach ($attention_rank as $key=>$val) {
                // 查看访问者是否关注该用户
                $attention_rank[$key]['is_attention'] = $this->pdo->count('tb_user_relation', "active_id={$visit_id} AND passive_id={$val['passive_id']}")?true:false;
                $attention_rank[$key] = array_merge($attention_rank[$key], $this->pdo->find("SELECT nickname, username, avatar FROM `tb_user` WHERE id={$val['passive_id']}"));
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> $attention_rank));
    }

}