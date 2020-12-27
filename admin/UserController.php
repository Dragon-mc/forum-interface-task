<?php
include './core/Controller.php';
include './utils/Uploader.php';

class UserController extends Controller
{
    public function login ($params) {
        // {"code":20000,"data":{"token":"admin-token"}}
        $res = $this->pdo->find("SELECT * FROM `tb_admin` WHERE binary `username`='{$params['username']}' AND binary `password`='{$params['password']}'");
        if (sizeof($res) == 0) {
            return json_encode(array('code'=>20001, 'message'=> '登录失败，账号或密码错误'));
        }
        // 生成随机的token值
        $token = str_replace('.', '', uniqid('token_', true));
        $returnData = array('code'=> 20000, 'data'=> array('token'=> $token));
        session_start();
        $role = ((int)$res['level']==0) ? 'admin' : 'low-admin';
        // 将用户信息存到 session的token键中
        $_SESSION[$token] = array(
            'roles' => [$role],
            'introduction' => $res['introduction'],
            'avatar' => $res['avatar'],
            'name' => $res['username'],
            'admin_id' => $res['id']
        );

        return json_encode($returnData);

    }

    public function info ($params) {
        // {"code":20000,"data":{"roles":["admin"],"introduction":"I am a super administrator","avatar":"https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif","name":"Super Admin"}}
        session_start();
        if (!!$_SESSION[$params['token']]) {
            $returnData = array(
                'code'=> 20000,
                'data'=> $_SESSION[$params['token']]
            );
        } else {
            $returnData = array(
                'code'=> 20000,
                'data'=> array(
                    'roles'=> ['admin'],
                    'introduction'=> 'I am a super administrator',
                    'avatar'=> 'https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif',
                    'name'=> 'Super Admin111',
                    'admin_id'=> 1
                )
            );
        }
        return json_encode($returnData);
    }

    public function logout ($param) {
        session_start();
        // 释放掉SESSION 中 存储的用户信息
        unset($_SESSION[$param['token']]);
        $returnData = array(
            'code'=> 20000,
            'data'=> 'success'
        );
        return json_encode($returnData);
    }

    public function fetchIndexInfo ($param) {
        $res = array('statistics'=> array(), 'system'=> array());
        $res['statistics']['user_num'] = $this->pdo->count('tb_user');
        $res['statistics']['post_num'] = $this->pdo->count('tb_post');
        $res['statistics']['comment_num'] = $this->pdo->count('tb_comment');
        $res['statistics']['feedback_num'] = $this->pdo->count('tb_feedback');
        $res['system']['environment'] = php_uname('s');
        $res['system']['webServer'] = $_SERVER['SERVER_SOFTWARE'];
        $res['system']['mysqlVersion'] = 'MYSQL'.$this->pdo->find('select version()')['version()'];
        $res['system']['phpVersion'] = 'PHP' . PHP_VERSION;

        return json_encode(array('code'=> 20000, 'data'=> $res));
    }

    // 上传用户头像
    public function uploadAvatar ($file) {
        $admin_id = $_POST['admin_id'];
        $avatar = str_replace('_', '.', $_POST['avatar']);
        // 将头像上传至服务器
        $upload = new Uploader();
        $uploadInfo = $upload->multiUpload($file);
        // 如果头像上传成功，则为用户设置头像
        if ($uploadInfo['code'] == 20000) {
            try {
                $this->pdo->update('tb_admin', array('avatar'=> $uploadInfo['data']['url']), "id={$admin_id}");
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

    public function setSessionAvatar ($param) {
        $avatar = str_replace('_', '.', $param['avatar']);
        session_start();
        $info = $_SESSION[$param['token']];
        $info['avatar'] = $avatar;
        $_SESSION[$param['token']] = $info;

        return json_encode(array('code'=> 20000));
    }

}