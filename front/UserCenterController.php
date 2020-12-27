<?php
include './core/Controller.php';
include './utils/Uploader.php';

class UserCenterController extends Controller
{
    // 获取用户信息
    public function getUserInfo ($param) {
        $id = (int)$param['id'];
        $visit_id = (int)$param['visit_id'];
        try {
            $res = $this->pdo->find("SELECT * FROM `tb_user` WHERE id={$id}");
            $res['attention_num'] = $this->pdo->count('tb_user_relation', "`active_id`={$id}");
            $res['fans_num'] = $this->pdo->count('tb_user_relation', "`passive_id`={$id}");
            $res['is_attention'] = $this->pdo->count('tb_user_relation', "`active_id`={$visit_id} AND `passive_id`={$id}")?true:false;
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        unset($res['password']);

        return json_encode(array('code'=> 20000, 'data'=> $res));
    }

    // 获取收藏列表
    public function fetchCollection ($param) {
        $id = $param['id'];
        $visit_id = $param['visit_id'];
        $limit = $param['limit'];
        $page = $param['page'];
        $skip = ($page - 1) * $limit;
        try {
            $total = $this->pdo->count('tb_collection', "user_id={$id}");
            $res = $this->pdo->select("SELECT `us`.username, `us`.nickname, `us`.avatar, `us`.id as user_id, `po`.*, `col`.time as collection_time FROM `tb_collection` as `col`, `tb_user` as `us`, `tb_post` as `po`  WHERE `col`.user_id={$id} AND `po`.user_id=`us`.id AND `col`.post_id=`po`.id ORDER BY `col`.time DESC LIMIT {$skip}, {$limit}");
            foreach ($res as $key=>$val) {
                // 获取帖子被评论次数
                $res[$key]['comment_times'] = $this->pdo->count('tb_comment', "post_id={$val['id']}");
                // 获取帖子被浏览次数
                $res[$key]['read_times'] = $this->pdo->count('tb_history', "post_id={$val['id']}");
                // 查看帖子是否被当前用户收藏
                $res[$key]['is_collection'] = $this->pdo->count('tb_collection', "post_id={$val['id']} AND user_id={$visit_id}")?true:false;
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> array('total'=> $total, 'items'=> $res)));
    }

    // 获取发布帖子的列表
    public function fetchPublish ($param) {
        $id = $param['id'];
        $limit = $param['limit'];
        $page = $param['page'];
        $skip = ($page - 1) * $limit;
        try {
            $total = $this->pdo->count('tb_post', "user_id={$id} AND status=1");
            $res = $this->pdo->select("SELECT * FROM `tb_post` WHERE user_id={$id} AND status=1 ORDER BY `time` DESC LIMIT {$skip}, {$limit}");
            foreach ($res as $key=>$val) {
                // 获取帖子被评论次数
                $res[$key]['comment_times'] = $this->pdo->count('tb_comment', "post_id={$val['id']}");
                // 获取帖子被浏览次数
                $res[$key]['read_times'] = $this->pdo->count('tb_history', "post_id={$val['id']}");
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> array('total'=> $total, 'items'=> $res)));
    }

    // 获取待发布帖子
    public function fetchDraftPost ($param) {
        $id = $param['id'];
        $limit = $param['limit'];
        $page = $param['page'];
        $skip = ($page - 1) * $limit;
        try {
            $total = $this->pdo->count('tb_post', "user_id={$id} AND status=0");
            $res = $this->pdo->select("SELECT * FROM `tb_post` WHERE user_id={$id} AND status=0 ORDER BY `time` DESC LIMIT {$skip}, {$limit}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> array('total'=> $total, 'items'=> $res)));
    }

    // 获取关注列表
    public function fetchAttention ($param) {
        $id = $param['id'];
        $visit_id = $param['visit_id'];
        $limit = $param['limit'];
        $page = $param['page'];
        $skip = ($page - 1) * $limit;
        try {
            $total = $this->pdo->count('tb_user_relation', "active_id={$id}");
            $res = $this->pdo->select("SELECT `user`.* FROM `tb_user_relation` as `ur`, `tb_user` as `user` WHERE `ur`.active_id={$id} AND `ur`.passive_id=`user`.id ORDER BY `ur`.time DESC LIMIT {$skip}, {$limit}");
            foreach ($res as $key=>$val) {
                $res[$key]['is_attention'] = $this->pdo->count('tb_user_relation', "active_id={$visit_id} AND passive_id={$val['id']}")?true:false;
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> array('total'=> $total, 'items'=> $res)));
    }

    // 获取粉丝列表
    public function fetchFans ($param) {
        $id = $param['id'];
        $visit_id = $param['visit_id'];
        $limit = $param['limit'];
        $page = $param['page'];
        $skip = ($page - 1) * $limit;
        try {
            $total = $this->pdo->count('tb_user_relation', "passive_id={$id}");
            $res = $this->pdo->select("SELECT `user`.* FROM `tb_user_relation` as `ur`, `tb_user` as `user` WHERE `ur`.passive_id={$id} AND `ur`.active_id=`user`.id ORDER BY `ur`.time DESC LIMIT {$skip}, {$limit}");
            foreach ($res as $key=>$val) {
                $res[$key]['is_attention'] = $this->pdo->count('tb_user_relation', "active_id={$visit_id} AND passive_id={$val['id']}")?true:false;
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> array('total'=> $total, 'items'=> $res)));
    }

    // 获取历史记录
    public function fetchHistory ($param) {
        $id = $param['id'];
        $limit = $param['limit'];
        $page = $param['page'];
        $skip = ($page - 1) * $limit;
        try {
            $total = $this->pdo->count('tb_history', "user_id={$id}");
            $res = $this->pdo->select("SELECT `us`.username, `us`.nickname, `us`.avatar, `us`.id as user_id, `po`.*, `his`.time as history_time FROM `tb_history` as `his`, `tb_user` as `us`, `tb_post` as `po`  WHERE `his`.user_id={$id} AND `po`.user_id=`us`.id AND `his`.post_id=`po`.id ORDER BY `his`.time DESC LIMIT {$skip}, {$limit}");
            foreach ($res as $key=>$val) {
                // 获取帖子被评论次数
                $res[$key]['comment_times'] = $this->pdo->count('tb_comment', "post_id={$val['id']}");
                // 获取帖子被浏览次数
                $res[$key]['read_times'] = $this->pdo->count('tb_history', "post_id={$val['id']}");
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000, 'data'=> array('total'=> $total, 'items'=> $res)));
    }

    // 修改用户信息
    public function modifyProfile ($param) {
        try {
            $this->pdo->update('tb_user', $param, "id={$param['id']}", true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> '信息修改失败'));
        }
        return json_encode(array('code'=> 20000));
    }

    // 上传用户头像
    public function uploadAvatar ($file) {
        $user_id = $_POST['user_id'];
        $avatar = str_replace('_', '.', $_POST['avatar']);
        // 将头像上传至服务器
        $upload = new Uploader();
        $uploadInfo = $upload->multiUpload($file);
        // 如果头像上传成功，则为用户设置头像
        if ($uploadInfo['code'] == 20000) {
            try {
                $this->pdo->update('tb_user', array('avatar'=> $uploadInfo['data']['url']), "id={$user_id}");
            } catch (Exception $e) {
                // 如果修改用户头像失败，则将上传的图片删除
                $upload->deletePhoto($uploadInfo['data']['url']);
                return json_encode(array('code'=> 20001, 'message'=> '修改头像失败'));
            }
        }
        // 删除原来的头像
        $upload->deletePhoto($avatar);

        return json_encode($uploadInfo);
    }

}