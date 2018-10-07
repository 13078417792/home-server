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
use \app\back\model\VideoCategory as VideoCategoryModel;
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
    public function list(){
        $user_info = $this->user_auth_info;
        $super = Rbac::isSuper($user_info['id']);
        $manager = Rbac::isManager($user_info['id']);

        $condition = [
            'is_private'=>0 // 禁止查看私密库视频
        ];

        // 超管或普通管理员可以接受[仅查看自己上传的视频(myself)]参数
        if($super || $manager){
            request()->post('myself/d',0,'intval') && $condition['uid'] = $user_info['id'];
            $private = !array_key_exists('private',$_POST)?true:(bool)$_POST['private'];
            if($super && !$manager && !$private){
                unset($condition['is_private']); // 超管可以查看任何用户的私密库视频（嘿嘿嘿）
            }
        }else{
            $condition['uid'] = $user_info['id'];
        }

        $result = model('UserVideo')->video_list($condition,$user_info);
        return json2(true,'',['result'=>$result]);
    }

    // 获取单个视频信息
    public function info(){
        $id = request()->post('id/d',null,'intval');
        $data = [
            'uid'=>$this->uid,
            'id'=>$id
        ];
        if($this->isSuper){
            unset($data['uid']);
        }
        $result = UserVideoModel::get($data);
        if(empty($result)){
            return json2(false,'数据不存在',['error'=>'视频已被删除']);
        }else{
            $result = toArray($result);
            $path = config('video.PATH').DS.$result['video_id'].DS.'screen';
            $result['screenshoots'] = [];
            if($result['is_merge'] && file_exists($path)){
                $screenshoots = scandir($path);
                array_shift($screenshoots);
                array_shift($screenshoots);
                foreach($screenshoots as $key => $value){
                    $result['screenshoots'][] = url('@link/images/video_thumb',['link'=>lock($result['video_id'].DS.'screen'.DS.$value)],'');
                }
            }

            return json2(true,'',['info'=>$result]);
        }
    }

    // 保存视频信息
    public function updateInfo(){
        $data = [

        ];
    }

    // 添加视频分类
    public function addCategory(){
        $data = [
            'name'=>request()->post('name/s',null,'strval'),
            'alias'=>request()->post('alias/s',null,'strval'),
            'pid'=>request()->post('pid/d',0,'intval'),
//            'pid'=>request()->post('category/a',[])
        ];

        $data['name'] = replace($data['name'],'/[^\x{4e00}-\x{9fa5}]/iu');  // 过滤非汉字字符
        $data['alias'] = replace($data['alias'],'/[^a-z-]/i');              // 过滤字符(仅匹配大小写英文字符和横杠)
        $data['pid'] = replace($data['pid'],'/[^\d]/');                     // 过滤字符(仅匹配数字)

//        $data['name'] = preg_replace('/[^\x{4e00}-\x{9fa5}]/iu','',trim($data['name']));    // 过滤非汉字字符
//        $data['alias'] = preg_replace('/[^a-z-]/i','',trim($data['alias']));                // 过滤字符(仅匹配大小写英文字符和横杠)
//        $data['pid'] = preg_replace('/[^\d]/','',trim($data['pid']));                       // 过滤字符(仅匹配数字)
        $result = model('VideoCategory')->add($data);
        return $result===true?json2(true,'添加成功'):json2(false,'添加失败',['error'=>$result]);
    }

    public function category(){
        $category = toArray(VideoCategoryModel::all(['status'=>1]));
        $tree = new \tree($category);
        $result = $tree->create()->result();
        return json2(true,'',['result'=>$result]);
    }

}