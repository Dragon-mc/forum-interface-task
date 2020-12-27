<?php
include './core/Controller.php';
include './utils/Cache.php';

class UserController extends Controller
{
    private $token;
    public function getVerifyCode ($param) {
        $this->token = $param['token'];
        return $this->vcode();
    }

    // 用户注册
    public function register ($param) {
        // 打开缓存
        $cache = new Cache(360, 'cache/');
        // 从缓存中取出验证码
        $vcode = $cache->get($param['token']);

        if (strtolower($vcode) != strtolower($param['vcode'])) {
            return json_encode(array('code'=> 20001, 'message'=> '验证码错误!'));
        }

        // 释放存储验证的缓存文件
        $cache->free($param['token']);
        // 将账号密码存入数据库 对密码进行md5加密
        $param['password'] = md5($param['password']);

        // 各项验证通过，将数据插入数据库
        try {
            $this->pdo->insert('tb_user', $param, true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> '注册失败，系统错误'));
        }

        return json_encode(array('code'=> 20000));
    }

    // 用户登录
    public function login ($param) {
        // 打开缓存
        $cache = new Cache(360, 'cache/');
        // 从缓存中取出验证码
        $vcode = $cache->get($param['token']);
        if (strtolower($vcode) != strtolower($param['vcode'])) {
            return json_encode(array('code'=> 20001, 'message'=> '验证码错误！'));
        }
        // 检验账号密码是否正确
        $pwd = md5($param['password']);
        try {
            $res = $this->pdo->select("SELECT * FROM `tb_user` WHERE binary `username`='{$param['username']}' AND binary `password`='{$pwd}'");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        if ($res) {
            // 释放存储验证的缓存文件
            $cache->free($param['token']);
            $res = $res[0];
            unset($res['password']);
            date_default_timezone_set("Asia/Shanghai");
            $this->pdo->update('tb_user', array('last_time'=> date("Y-m-d H:i:s")), "id={$res['id']}");
            return json_encode(array('code'=> 20000, 'data'=> $res));
        } else {
            return json_encode(array('code'=> 20001, 'message'=> '账号密码有误！'));
        }
    }

    // 释放验证码的存储文件
    public function freeCache ($param) {
        $cache = new Cache(360, 'cache/');
        $cache->free($param['token']);
        return json_encode(array('code'=> 20000));
    }

    // 检查用户名是否存在
    public function checkUserExist ($param) {
        $username = $param['username'];
        $res = $this->pdo->select("SELECT count(*) as NUM FROM `tb_user` WHERE binary `username`='{$username}'")[0]['NUM'];
        if ($res == 0) {
            return json_encode(array('code'=> 20000, 'data'=> false));
        } else {
            return json_encode(array('code'=> 20000, 'data'=> true));
        }
    }

    // 取消关注指定用户
    public function cancelAttention ($param) {
        try {
            $this->pdo->delete('tb_user_relation', "active_id={$param['active_id']} AND passive_id={$param['passive_id']}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> '取消关注失败'));
        }

        return json_encode(array('code'=> 20000));
    }

    // 关注指定用户
    public function attention ($param) {
        try {
            $this->pdo->insert('tb_user_relation', $param, true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> '关注失败'));
        }

        return json_encode(array('code'=> 20000));
    }

    // 取消收藏指定帖子
    public function calcelCollection ($param) {
        try {
            $this->pdo->delete('tb_collection', "post_id={$param['post_id']} AND user_id={$param['user_id']}");
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> '取消收藏失败'));
        }

        return json_encode(array('code'=> 20000));
    }

    // 收藏指定帖子
    public function collection ($param) {
        try {
            $this->pdo->insert('tb_collection', $param, true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> '收藏失败'));
        }

        return json_encode(array('code'=> 20000));
    }

    // 添加浏览历史，并增加阅读次数
    public function history ($param) {
        try {
            $res = $this->pdo->find("SELECT id FROM `tb_history` WHERE user_id={$param['user_id']} AND post_id={$param['post_id']}");
            if ($res) {
                // 如果记录存在
                $this->pdo->update('tb_history', array('time'=> $param['time']), "id={$res['id']}");
            } else {
                // 记录不存在
                $this->pdo->insert('tb_history', $param, true);
            }
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000));
    }

    // 反馈意见
    public function feedback ($param) {
        try {
            $this->pdo->insert('tb_feedback', $param, true);
        } catch (Exception $e) {
            return json_encode(array('code'=> 20001, 'message'=> $e->getMessage()));
        }

        return json_encode(array('code'=> 20000));
    }

    private function vcode($width=120,$height=40,$stringCount=4,$pixel=200,$line=20) {
//        header('Content-type:image/jpeg;charset=utf-8');
        $img = imagecreatetruecolor($width,$height);
        $colorBg = imagecolorallocate($img,rand(200,255),rand(200,255),rand(200,255));
        $colorBorder = imagecolorallocate($img,255,255,255);
        $colorPixel = imagecolorallocate($img,rand(50,150),rand(50,150),rand(50,150));
        $colorFont = imagecolorallocate($img,rand(25,100),rand(25,100),rand(25,100));
        imagefill($img,0,0,$colorBg);
        imagerectangle($img,0,0,$width-1,$height-1,$colorBorder);
        for($i=0;$i<$pixel;$i++){
            imagesetpixel($img,rand(0,$width-1),rand(0,$height-1),$colorPixel);
        }
        for($i=0;$i<$line;$i++){
            imageline($img,rand(0,$width-1),rand(0,$height-1),rand(0,$width-1),rand(0,$height-1),$colorPixel);
        }
        $x = $width/$stringCount/2;
        $interval = ($width-$width/$stringCount)/$stringCount;
        $url = dirname($_SERVER['SCRIPT_FILENAME']);
        $string = "";
        for($i=0;$i<$stringCount;$i++){
            $now = chr(rand(65,90));
            $string .= $now;
            imagettftext($img,$interval,rand(-20,20),$x,rand($height-$height/15,$height-$height/4),$colorFont,"{$url}/src/stampa.ttf",$now);
            $x += $interval;
        }
//        imagettftext($img,20,0,0,$height-1,$colorFont,'/StudyInHome/www/php/CheckCode/font/stampa.ttf',$string);

        ob_start();

        imagejpeg($img);
        $image_data = ob_get_contents();

        ob_end_clean();

        $image_data_base64 = base64_encode($image_data);

        imagedestroy($img);

        // 将验证字符串 存入到缓存中
        $cache = new Cache(360, 'cache/');
        $cache->put($this->token, $string);

        return json_encode(array('code'=> 20000, 'data'=> $image_data_base64));
    }

}