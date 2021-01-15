<?php
include './core/Controller.php';

class IndexController extends Controller
{
    // 获取首页推荐列表信息
    public function fetchRecommendPostList ($param) {
        $limit = $param['limit'];
        $skip = $param['skip'];
        try {
            $sql = "SELECT `post`.*, `user`.nickname, `user`.username, `user`.avatar
                    FROM `tb_post` as `post`
                    JOIN `tb_user` as `user` ON `post`.user_id=`user`.id
                    WHERE `post`.status=1
                    ORDER BY `post`.time DESC
                    LIMIT {$skip}, {$limit}";
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
        return json_encode(array('code'=> 20000, 'data'=> $res));
    }

    // 获取轮播图列表
    public function fetchCarouselList () {
        // 随机抽取5个拥有图片数据的帖子
        try {
            $sql = "SELECT * FROM tb_post WHERE `content` LIKE '%<img%src=%>' ORDER BY rand() LIMIT 5";
            $res = $this->pdo->select($sql);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000, 'data'=> $res));
    }

    // 获取首页首页排行榜列表
    public function fetchRankList () {
        $res = array();
        try {
            $sql = "SELECT post_id, count(id) as `read_times`
                    FROM `tb_history`
                    GROUP BY post_id
                    ORDER BY `read_times` DESC
                    LIMIT 10";
            $browse_desc = $this->pdo->select($sql);
            $browse_rank = array();
            foreach ($browse_desc as $key=>$val) {
                // 获取每条浏览排行中 帖子信息 和 用户头像
                $sql = "SELECT `post`.title, `post`.status, `user`.avatar, `post`.id
                        FROM `tb_post` as `post`
                        JOIN `tb_user` as `user` ON `post`.user_id=`user`.id
                        WHERE `post`.id={$val['post_id']}";
                $browse_rank[$key] = $this->pdo->find($sql);
                if ($browse_rank[$key]['status'] == 2) unset($browse_rank[$key]);
            }
            $sql = "SELECT post_id, count(id) as `commented_times`
                    FROM `tb_comment`
                    GROUP BY post_id
                    ORDER BY `commented_times` DESC LIMIT 10";
            $comment_rank = $this->pdo->select($sql);
            foreach ($comment_rank as $key=>$val) {
               // 获取每个帖子的详情 和 用户信息
                $sql = "SELECT `user`.nickname, `user`.username, `user`.avatar, `user`.sign, `post`.title
                        FROM `tb_post` as `post`
                        JOIN `tb_user` as `user` ON `post`.user_id=`user`.id
                        WHERE `post`.id={$val['post_id']}";
                $comment_rank[$key]['details'] = $this->pdo->find($sql);
            }
            $sql = "SELECT passive_id, count(*) as `passive_attention_num`
                    FROM `tb_user_relation`
                    GROUP BY passive_id
                    ORDER BY `passive_attention_num` DESC
                    LIMIT 10";
            $attention_rank = $this->pdo->select($sql);
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

    // 获取分类列表
    public function fetchCateList () {
        try {
            $res = $this->pdo->select("SELECT id, name FROM `tb_main_cate`");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> $res));
    }

    // 获取浏览排行列表
    public function fetchBrowseRank ($param) {
        $limit = $param['limit'];
        $skip = $param['skip'];
        try {
            $sql = "SELECT post_id, count(id) as `read_times`
                    FROM `tb_history`
                    GROUP BY post_id
                    ORDER BY `read_times` DESC
                    LIMIT {$skip}, {$limit}";
            $browse_desc = $this->pdo->select($sql);
            foreach ($browse_desc as $key=>$val) {
                // 获取每条浏览排行中 帖子信息 和 用户头像
                $sql = "SELECT `post`.title, `post`.status, `user`.avatar, `user`.nickname, `user`.username, `user`.id as `user_id`
                        FROM `tb_post` as `post`
                        JOIN `tb_user` as `user` ON `post`.user_id=`user`.id
                        WHERE `post`.id={$val['post_id']}";
                $browse_desc[$key] = array_merge($browse_desc[$key], $this->pdo->find($sql));
                if ((int)$browse_desc[$key]['status'] === 2) unset($browse_desc[$key]);
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> $browse_desc));
    }

    // 获取评论排行列表
    public function fetchCommentRank ($param) {
        $limit = $param['limit'];
        $skip = $param['skip'];
        try {
            $sql = "SELECT post_id, count(id) as `commented_times`
                    FROM `tb_comment`
                    GROUP BY post_id
                    ORDER BY `commented_times` DESC
                    LIMIT {$skip}, {$limit}";
            $comment_rank = $this->pdo->select($sql);
            foreach ($comment_rank as $key=>$val) {
                // 获取每条帖子的浏览次数
                $comment_rank[$key]['read_times'] = $this->pdo->count('tb_history', "post_id={$val['post_id']}");
                // 获取每个帖子的详情 和 用户信息
                $sql = "SELECT `user`.nickname, `user`.username, `user`.avatar, `user`.id as `user_id`, `post`.title, `post`.content, `post`.time
                        FROM `tb_post` as `post`
                        JOIN `tb_user` as `user` ON `post`.user_id=`user`.id
                        WHERE `post`.id={$val['post_id']}";
                $comment_rank[$key] = array_merge($comment_rank[$key], $this->pdo->find($sql));
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000, 'data'=> $comment_rank));
    }

    // 获取关注排行列表
    public function fetchAttentionRank ($param) {
        $visit_id = $param['visit_id'];
        $limit = $param['limit'];
        $skip = $param['skip'];
        try {
            $sql = "SELECT passive_id, count(id) as `fans_num`
                    FROM `tb_user_relation`
                    GROUP BY passive_id
                    ORDER BY `fans_num` DESC
                    LIMIT {$skip}, {$limit}";
            $attention_rank = $this->pdo->select($sql);
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