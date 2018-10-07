<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/10/5
 * Time: 22:04
 */

namespace app\link\controller;


class Images{

    // 计算base64
    static public function createBase64($path){
        $mime_type= mime_content_type($path);
        $base64_data = base64_encode(file_get_contents($path));
        $base64_file = 'data:'.$mime_type.';base64,'.$base64_data;
        return $base64_file;
    }

    // 输出图片
    static public function img($path){
        $info = getimagesize($path);
        $ext = image_type_to_extension($info[2],false);
        $fn = "imagecreatefrom{$ext}";
        $res = $fn($path);
        $mime = image_type_to_mime_type($info[2]);
        $getImgInfo = "image{$ext}";
        $getImgInfo($res,null);
        imagedestroy($res);
        header('Content-Type:'.$mime);
        exit;
    }

    public function video_thumb(){
        config('app_trace',false);
        config('app_debug',false);
        $link = request()->get('link/s',null,'strval');
        if(empty($link)){
            abort(404);
        }
        $path = config('video.PATH').DS.de_lock($link);
        if(!file_exists($path)){
            abort(404);
        }
        self::img($path);
    }
}