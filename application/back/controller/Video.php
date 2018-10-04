<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/23
 * Time: 22:37
 */

namespace app\back\controller;

use \think\Cache;
use \think\Request;
use \think\Loader;
use \app\back\model\Video as VideoModel;
use \app\back\model\UserVideo as UserVideoModel;
use \Rbac\Rbac;
class Video extends Base{

    protected $path = '';
    static public $token_expires = 7200;

    /* 即将废弃 */
//    static public $upload_token = 'upload_video_token_';
//    static public $relation_key = 'video_name_relation_';
    /* 即将废弃 */

    // 缓存：循环上传的视频片段信息
    static public $eachUpload_info = 'eachUploadVideo_';

    // 上传必须的token
    static public $upload_token = 'uploadVideo_';

    protected function _initialize(){
        parent::_initialize();
        $this->path = config('video.PATH');
    }

    // 生成上传片段必须的token
//    public function upload_token(){
//        $token = $this->base_token('upload_video');
//        cache(self::$upload_token.$this->user_auth_info['id'],$token,3600*2);
//        return json2(true,'',['token'=>$token]);
//    }

    // 验证上传片段的token
    static public function validate_upload_token($uid,$token,$md5){
        $cache = cache(self::$upload_token.$uid.'_'.$token);
//        dd($cache);
//        dd($md5);
        return $cache===$md5;
    }

    protected function base_token($salt=''){
        if($salt && gettype($salt)==='string' && strlen($salt)>15){
            $salt = substr($salt,0,14);
        }else{
            $salt = '';
        }
        $token = md5(uniqid().$this->auth.Request::instance()->token('video').$salt);
        return $token;
    }

    public function upload2(){
        $data = [
            'user_info'=>$this->user_auth_info,
            'index'=>request()->post('index/d',null),
            'end'=>request()->post('end/d',null,'intval'),
//            'title'=>request()->post('title/s',null,'strval'),
            'file'=>request()->file('file')
        ];
        $token = request()->post('token/s',null,'strval');
        $model = model('Video');
        $result = $model->upload($data,$token);
        return $result===true?json2(true,'上传成功',['id'=>$model->video_id]):json2(false,'上传失败',['error'=>$result]);
    }

    public function upload3(){

        $data = [
            'md5'=>request()->post('unique/s',null,'strval'), // 文件MD5,
            'file'=>request()->file('file'),
            'index'=>request()->post('index/d',null,'intval'),
            'end'=>request()->post('end/d',null,'intval')
        ];
        $token = request()->post('token/s',null,'strval');

        $uid = $this->user_auth_info['id'];

        // 验证上传token
        if(empty($data['md5']) || !$token || self::validate_upload_token($uid,$token,$data['md5']===false)){
            return json2(false,'上传失败',['error'=>'上传失败','valid'=> self::validate_upload_token($uid,$token,$data['md5']) ]);
        }

        $model = model('Video');
        $result = $model->upload($data,$token,$this->user_auth_info);
        return $result===true?json2(true,'上传成功',['id'=>$model->video_id]):json2(false,'上传失败',['error'=>$result]);
    }

    // 上传前的检测，是否秒传,并返回上传token
    public function beforeUploadCheck(){
        $md5 = request()->post('md5/s',null,'strval');
        if(!$md5){
            return json2(false,'校验失败',['error'=>'没有文件校验值']);
        }

        $detail = VideoModel::get($md5);
        $token = $this->base_token('upload_video');
        cache(self::$upload_token.$this->user_auth_info['id'].'_'.$token,$md5,3600*2); // 上传token
        if(!empty($detail)){
            // 秒传
            $exist = $detail->user()->where(['is_merge'=>1])->find();
            $detail->user()->save(['uid'=>$this->user_auth_info['id'],'status'=>-1,'is_merge'=>$exist?1:0,'time'=>$_SERVER['REQUEST_TIME']]);
            return json2(true,'',['quick'=>true,'token'=>$token]);
        }
        $path = config('video.PATH').DS.$md5;
        if(file_exists($path)){
            $list = @scandir($path);
            array_shift($list);
            array_shift($list);
            return json2(true,'',['quick'=>true,'continue'=>true,'list'=>$list,'token'=>$token]);
        }else{
            return json2(true,'',['quick'=>false,'continue'=>false ,'token'=>$token]);
        }
    }

    // 获取视频列表
    public function video_list(){
        $data = [];
        $user = $this->user_auth_info;
        $super = Rbac::isSuper($user['id']);
        $manager = Rbac::isManager($user['id']);

        // 超管或普通管理员可以接受[仅查看自己上传的视频(myself)]参数
        if($super || $manager){
            $data['myself'] = request('myself/d',0,'intval')?true:false;
        }

    }

}