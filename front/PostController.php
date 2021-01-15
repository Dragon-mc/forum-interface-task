<?php
include './core/Controller.php';
include './utils/Uploader.php';

class PostController extends Controller
{
    // 获取帖子信息
    public function fetchPostInfo ($param) {
        // 帖子id
        $id = $param['id'];
        // 参观者id，即当前访问的用户
        $visit_id = $param['visit_id'];
        try {
            $sql = "SELECT `po`.*, `sc`.name as cate_name
                    FROM `tb_post` as `po`
                    JOIN `tb_sub_cate` as `sc` ON `po`.sub_id=`sc`.id
                    WHERE `po`.id={$id}";
            $res = $this->pdo->find($sql);
            $res['comment_times'] = $this->pdo->count('tb_comment', "post_id={$res['id']}");
            $res['read_times'] = $this->pdo->count('tb_history', "post_id={$res['id']}");
            $res['collection_times'] = $this->pdo->count('tb_collection', "post_id={$res['id']}");
            $res['is_collection'] = $this->pdo->count('tb_collection', "post_id={$res['id']} AND user_id={$visit_id}")?true:false;
            $res['user_info'] = $this->pdo->find("SELECT * FROM `tb_user` WHERE id={$res['user_id']}");
            $res['user_info']['attention_num'] = $this->pdo->find("SELECT count(*) as NUM FROM `tb_user_relation` WHERE `active_id`={$res['user_id']}")['NUM'];
            $res['user_info']['fans_num'] = $this->pdo->find("SELECT count(*) as NUM FROM `tb_user_relation` WHERE `passive_id`={$res['user_id']}")['NUM'];
            $res['user_info']['comment_num'] = $this->pdo->find("SELECT count(*) as NUM FROM `tb_comment` WHERE `user_id`={$res['user_id']}")['NUM'];
            $res['user_info']['collection_num'] = $this->pdo->find("SELECT count(*) as NUM FROM `tb_collection` WHERE `user_id`={$res['user_id']}")['NUM'];
            $res['user_info']['is_attention'] = $this->pdo->find("SELECT count(*) as NUM FROM `tb_user_relation` WHERE active_id={$visit_id} AND passive_id={$res['user_id']}")['NUM']?true:false;
            unset($res['user_info']['password']);
            $sql = "SELECT `com`.*, `us`.nickname, `us`.username, `us`.avatar
                    FROM `tb_comment` as `com`
                    JOIN `tb_user` as `us` ON `com`.user_id=`us`.id
                    WHERE `com`.post_id={$id}
                    ORDER BY `com`.time DESC";
            $comment_info = $this->pdo->select($sql);
            foreach ($comment_info as $key=>$val) {
                $sql = "SELECT `ur`.*, `us1`.nickname, `us1`.username, `us1`.avatar, `us2`.nickname as `passive_nickname`, `us2`.username as `passive_username`
                        FROM `tb_user_reply` as `ur`
                        JOIN `tb_user` as `us1` ON `ur`.user_id=`us1`.id
                        JOIN `tb_user` as `us2` ON `ur`.passive_user_id=`us2`.id
                        WHERE `ur`.comment_id={$val['id']}
                        ORDER BY `ur`.time DESC";
                $comment_info[$key]['reply_info'] = $this->pdo->select($sql);
            }
            $res['comment_info'] = $comment_info;
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> $res));
    }

    // 获取帖子评论信息
    public function fetchCommentInfo ($param) {
        $post_id = $param['post_id'];
        $limit = $param['limit'];
        $page = $param['currentPage'];
        $skip = ($page - 1) * $limit;
        try {
            $total = $this->pdo->count('tb_comment', "post_id={$post_id}");
            $sql = "SELECT `com`.*, `us`.nickname, `us`.username, `us`.avatar
                    FROM `tb_comment` as `com`
                    JOIN `tb_user` as `us` ON `com`.user_id=`us`.id
                    WHERE `com`.post_id={$post_id}
                    ORDER BY `com`.time DESC
                    LIMIT {$skip}, {$limit}";
            $comment_info = $this->pdo->select($sql);
            foreach ($comment_info as $key=>$val) {
                $sql = "SELECT `ur`.*, `us1`.nickname, `us1`.username, `us1`.avatar, `us2`.nickname as `passive_nickname`, `us2`.username as `passive_username`
                        FROM `tb_user_reply` as `ur`
                        JOIN `tb_user` as `us1` ON `ur`.user_id=`us1`.id
                        JOIN `tb_user` as `us2` ON `ur`.passive_user_id=`us2`.id
                        WHERE `ur`.comment_id={$val['id']}
                        ORDER BY `ur`.time ASC";
                $comment_info[$key]['reply_info'] = $this->pdo->select($sql);
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> array('total'=> $total, 'items'=> $comment_info)));
    }

    // 评论帖子
    public function comment ($param) {
        try {
            $this->pdo->insert('tb_comment', $param, true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> '评论失败'));
        }
        return json_encode(array('code'=> 20000));
    }

    // 回复帖子评论
    public function reply ($param) {
        try {
            $this->pdo->insert('tb_user_reply', $param, true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> '回复失败'));
        }
        return json_encode(array('code'=> 20000));
    }

    // 删除帖子
    public function deletePost ($param) {
        $post_id = $param['post_id'];
        try {
//            $this->pdo->delete('tb_post', "id={$post_id}");
            $this->pdo->update('tb_post', array('status'=> 2), "id={$post_id}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> '回复失败'));
        }
        return json_encode(array('code'=> 20000));
    }

    // 发布帖子时，上传图片
    public function uploadImg ($file) {
        $upload = new Uploader();
        $imageInfo = $upload->multiUpload($file);
        return json_encode($imageInfo);
    }

    // 发布帖子
    public function postPublish ($param) {
        $param['content'] = str_replace('_', '.', $param['content']);
        try {
            $this->pdo->insert('tb_post', $param, true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }

    // 编辑帖子
    public function postEdit ($param) {
        $param['content'] = str_replace('_', '.', $param['content']);
        try {
            $this->pdo->update('tb_post', $param, "id={$param['id']}", true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000));
    }
}