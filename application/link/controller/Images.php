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
            response('images not found',404)->send();
            return;
        }
        $path = config('video.PATH').DS.de_lock($link);
        if(!file_exists($path)){
            response('images not found',404)->send();
            return;
        }
        self::img($path);
    }

    /*
     * 生成图片链接
     * @params $path string 生成链接的图片存放绝对路径
     * */
    static public function buildUrl($path){

        $path = str_replace(config('common')['path']['image'],'',$path);
        if(empty($path)){
            return false;
        }
        $param = lock($path);
        config('url_common_param',false);
        return url('/link/images/get',['name'=>$param],'');
    }

    /*
     * 判断图片是否存在
     * @params $path string 图片存放绝对路径
     * */
    static public function hasImages($path){
        $child_path = str_replace(config('common')['path']['image'],'',$path);
        if(empty($child_path)){
            return false;
        }
        return file_exists($path);
    }

    public function get(){
        config('app_trace',false);
        config('app_debug',false);
        config('url_common_param',false);
        $name = request()->param('name');
        $path = config('common')['path']['image'];
        $path = $path.DS.de_lock($name);
        if(!file_exists($path) || empty($name)){
            response('images not found',404)->send();
            return;
        }
        self::img($path);
    }
}