<?php

class Uploader
{
    private $uploadConfig;

    public function __construct() {
        $this->uploadConfig = include './config/upload.config.php';
    }

    public function multiUpload($FILE)
    {
        date_default_timezone_set('PRC');//获取当前时间

        //上传文件目录获取

        $date = date('Ymd', time());

        $dir = "/upload/{$date}/";
        $abs_dir = $this->uploadConfig['product_path'] . $dir;

        $file_details = pathinfo($FILE["file"]["name"]);
        $file_name = str_replace('.', '', uniqid(rand(0, 9), true)) . '.' . $file_details['extension'];
//初始化返回数组

        $arr = array(

            'code' => 20000,

            'message' => '',

            'data' => array(

                'url' => $this->uploadConfig['domain'] . $dir . $file_name

            ),

        );


        $file_info = $FILE['file'];

        $file_error = $file_info['error'];

        if (!is_dir($abs_dir)) {//判断目录是否存在

            mkdir($abs_dir, 0777, true);//如果目录不存在则创建目录

        };

        $file = $abs_dir . $FILE["file"]["name"];

        if (!file_exists($file)) {

            if ($file_error == 0) {

                if (move_uploaded_file($FILE["file"]["tmp_name"], $abs_dir . $file_name)) {

                    $arr['message'] = "上传成功";

                } else {

                    $arr['code'] = 20001;
                    $arr['message'] = "上传失败";

                }

            } else {

                $arr['code'] = 20001;
                switch ($file_error) {

                    case 1:

                        $arr['message'] = '上传文件超过了PHP配置文件中upload_max_filesize选项的值';

                        break;

                    case 2:

                        $arr['message'] = '超过了表单max_file_size限制的大小';

                        break;

                    case 3:

                        $arr['message'] = '文件部分被上传';

                        break;

                    case 4:

                        $arr['message'] = '没有选择上传文件';

                        break;

                    case 6:

                        $arr['message'] = '没有找到临时文件';

                        break;

                    case 7:

                    case 8:

                        $arr['message'] = '系统错误';

                        break;

                }

            }

        } else {

            $arr['code'] = 20001;

            $arr['message'] = "当前目录中，文件" . $file . "已存在";

        }


        return $arr;
    }

    public function deletePhoto ($path)
    {
        // 此时的$path是文件的展示路径
        // 使用.替换domain，将文件的展示路径转换为 当前文件所在服务器的相对路径
        $photoPath = str_replace($this->uploadConfig['domain'], $this->uploadConfig['product_path'], $path);
        if (file_exists($photoPath)) {
            // 如果文件存在，则将文件删除
            unlink($photoPath);
        }
    }

}