<?php
include './core/Controller.php';

class CategoryController extends Controller
{
    // 获取分类页面中指定分类的所有帖子
    public function fetchPost ($param) {
        $limit = $param['limit'];
        $skip = $param['skip'];
        function getId ($val) {
            return $val['id'];
        }
        try {
            if (isset($param['main_cate'])) {
                // 处理获取主分类中所有帖子
                $all_sub_cate_id= $this->pdo->select("SELECT id FROM `tb_sub_cate` WHERE main_id={$param['main_cate']}");
                $sub_id_string = implode(", ", array_map("getId", $all_sub_cate_id));
                $res = $this->pdo->select("SELECT `post`.*, `user`.nickname, `user`.username, `user`.avatar FROM `tb_post` as `post`, `tb_user` as `user` WHERE `post`.sub_id in({$sub_id_string}) AND `post`.user_id=`user`.id AND `post`.status=1 LIMIT {$skip}, {$limit}");
            } elseif (isset($param['sub_cate'])) {
                // 处理获取次分类中所有帖子
                $res = $this->pdo->select("SELECT `post`.*, `user`.nickname, `user`.username, `user`.avatar FROM `tb_post` as `post`, `tb_user` as `user` WHERE `post`.sub_id={$param['sub_cate']} AND `post`.user_id=`user`.id AND `post`.status=1 LIMIT {$skip}, {$limit}");
            } else {
                // 当没有指定分类时
                $res = $this->pdo->select("SELECT `post`.*, `user`.nickname, `user`.username, `user`.avatar FROM `tb_post` as `post`, `tb_user` as `user` WHERE `post`.user_id=`user`.id AND `post`.status=1 LIMIT {$skip}, {$limit}");
            }

            foreach ($res as $key=>$val) {
                // 获取到每一个帖子的被评论次数
                $res[$key]['comment_times'] = $res[$key]['comment_times'] = $this->pdo->count('tb_comment', "post_id={$val['id']}");
                // 获取帖子被浏览次数
                $res[$key]['read_times'] = $this->pdo->count('tb_history', "post_id={$val['id']}");
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000, 'data'=> $res));
    }

    // 获取分类页面所有分类
    public function fetchCategory () {
        try {
            $res = $this->pdo->select("SELECT `id`, `name` FROM `tb_main_cate`");
            foreach ($res as $key=>$val) {
                $res[$key]['sub_cate'] = $this->pdo->select("SELECT `id`, `name` FROM `tb_sub_cate` WHERE `main_id`={$val['id']}");
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }
        return json_encode(array('code'=> 20000, 'data'=> $res));
    }

}