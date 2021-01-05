<?php
include './core/Controller.php';
include './utils/Uploader.php';

class PostController extends Controller
{
    public function fetchPostInfo ($param) {
        // 帖子id
        $id = $param['id'];
        // 参观者id，即当前访问的用户
        $visit_id = $param['visit_id'];
        try {
            $res = $this->pdo->find("SELECT `po`.*, `sc`.name as cate_name FROM `tb_post` as `po`, `tb_sub_cate` as `sc` WHERE `po`.id={$id} AND `po`.sub_id=`sc`.id");
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
            $comment_info = $this->pdo->select("SELECT `com`.*, `us`.nickname, `us`.username, `us`.avatar FROM `tb_comment` as `com`, `tb_user` as `us` WHERE `com`.post_id={$id} AND `com`.user_id=`us`.id ORDER BY `com`.time DESC");
            foreach ($comment_info as $key=>$val) {
                $comment_info[$key]['reply_info'] = $this->pdo->select("SELECT `ur`.*, `us1`.nickname, `us1`.username, `us1`.avatar, `us2`.nickname as `passive_nickname`, `us2`.username as `passive_username` FROM `tb_user_reply` as `ur`, `tb_user` as `us1`, `tb_user` as `us2` WHERE `ur`.comment_id={$val['id']} AND `ur`.user_id=`us1`.id AND `ur`.passive_user_id=`us2`.id ORDER BY `ur`.time DESC");
            }
            $res['comment_info'] = $comment_info;
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> $res));
    }

    public function fetchCommentInfo ($param) {
        $post_id = $param['post_id'];
        $limit = $param['limit'];
        $page = $param['currentPage'];
        $skip = ($page - 1) * $limit;
        try {
            $total = $this->pdo->count('tb_comment', "post_id={$post_id}");
            $comment_info = $this->pdo->select("SELECT `com`.*, `us`.nickname, `us`.username, `us`.avatar FROM `tb_comment` as `com`, `tb_user` as `us` WHERE `com`.post_id={$post_id} AND `com`.user_id=`us`.id ORDER BY `com`.time DESC LIMIT {$skip}, {$limit}");
            foreach ($comment_info as $key=>$val) {
                $comment_info[$key]['reply_info'] = $this->pdo->select("SELECT `ur`.*, `us1`.nickname, `us1`.username, `us1`.avatar, `us2`.nickname as `passive_nickname`, `us2`.username as `passive_username` FROM `tb_user_reply` as `ur`, `tb_user` as `us1`, `tb_user` as `us2` WHERE `ur`.comment_id={$val['id']} AND `ur`.user_id=`us1`.id AND `ur`.passive_user_id=`us2`.id ORDER BY `ur`.time ASC");
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> array('total'=> $total, 'items'=> $comment_info)));
    }

    public function comment ($param) {
        try {
            $this->pdo->insert('tb_comment', $param, true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> '评论失败'));
        }
        return json_encode(array('code'=> 20000));
    }

    public function reply ($param) {
        try {
            $this->pdo->insert('tb_user_reply', $param, true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> '回复失败'));
        }
        return json_encode(array('code'=> 20000));
    }

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