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
use \app\back\model\VideoTag as VideoTagModel;
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
    public $infoActionType = 'update';

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
        return $result===true?json2(true,'上传成功'):json2(false,'上传失败',['error'=>$result]);
    }

    public function upload4(){

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
            return json2(false,'操作失败',['error'=>'上传失败','valid'=> self::validate_upload_token($uid,$token,$data['md5']) ]);
        }

        // 开始验证
        if(empty($data['file'])){
            return json2(false,'操作失败',['error'=>'没有文件被上传']);
        }
        $file = $data['file'];
        if(!$file instanceof \think\File){
            return json2(false,'操作失败',['error'=>'非法的文件']);
        }
        if((empty($data['index']) && $data['index']!=0) || empty($data['end'])){
            return json2(false,'操作失败',['error'=>'缺少必须数据']);
        }

        $file_dir = config('video.PATH').DS.$data['md5'];
        $info = $file->validate(['ext'=>'mp4,rmvb,flv,mkv,avi']);
        if($info){
            if(!file_exists($file_dir.DS.$data['index'].'.'.$info->getExtension())){
                $info->move($file_dir,$data['index']);
            }
            $extends = [];
            if($data['end']===$data['index']){
                $model = VideoModel::get($data['md5']);
                if(empty($model)){
                    $model = model('Video');
                    $model->data(['id'=>$data['md5'],'end'=>$data['end'],'time'=>$_SERVER['REQUEST_TIME'],'path'=>$file_dir])->save();
                    $relation = $model->user();
                }else{
                    $relation = $model->user();
                }

                $relation = $relation->save(['uid'=>$uid,'status'=>-1,'is_merge'=>0,'time'=>$_SERVER['REQUEST_TIME']]);
                $extends['id'] = $relation->id;
                if( !file_exists($file_dir.DS.'merge')){
                    $ch = curl_init('http://127.0.0.1:8999/merge-video');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type' => 'multipart/form-data',
                    ]);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, ['path' => $data['md5']]);
                    curl_exec($ch);
                    curl_close($ch);
                }
            }

            return json2(true,'操作成功',$extends);
        }else{
            return json2(false,'操作失败',['error'=>$info->getError()]);
        }
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
            $save_data = $detail->user()->save(['uid'=>$this->user_auth_info['id'],'status'=>-1,'is_merge'=>$exist?1:0,'time'=>$_SERVER['REQUEST_TIME']]);

            return json2(true,'',['quick'=>true,'token'=>$token,'id'=>$save_data['id']]);
        }
        $path = config('video.PATH').DS.$md5;
        if(is_dir($path)){
            $list = @scandir($path);
            array_shift($list);
            array_shift($list);
            foreach($list as $key => $value){
                if(is_dir($value)){
                    unset($list[$key]);
                    continue;
                }
                $extname = pathinfo($path.DS.$value,PATHINFO_EXTENSION);
                if(!in_array($extname,['mp4','avi','rmvb','flv','mkv','avi'])){
                    unset($list[$key]);
                    continue;
                }
            }
            $model = model('Video');
//            $model->data(['id'=>$md5,'end'=>])->save();
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
        }else{
            $condition['uid'] = $user_info['id'];
        }

        if(array_key_exists('status',$_POST)){
            $condition['status'] = request()->post('status/d',1,'intval');
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

            $tags = explode(',',$result['tag']);
            $tags = VideoTagModel::where([
                'id'=>['in',$tags]
            ])->column('id,name');
            $result['tag'] = implode(',',$tags);


            $result['is_private'] = !!$result['is_private'];
            return json2(true,'',['info'=>$result]);
        }
    }

    // 保存视频信息
    public function updateInfo(){
        $data = [
            'id'=>request()->post('id/d',null,'intval'),
            'title'=>request()->post('title/s',null,'strval'),
            'tag'=>request()->post('tag/a',[]),
            'category'=>request()->post('category/a',[]),
            'intro'=>request()->post('intro/s',null,'strval'),
            'is_private'=>request()->post('is_private/d',0,'intval'),
            'is_share'=>request()->post('is_share',0,'intval'),
            'share'=>request()->post('share/s',null,'strval'),
            'thumb'=>request()->post('thumb/s',null,'strval')
        ];

        foreach($data['tag'] as &$value){
            $value = replace($value,'/[^\x{4e00}-\x{9fa5}a-z0-9]/iu');
            unset($value);
        }

//        dd($data['category']);
        foreach($data['category'] as $key => &$value){
            $value = replace($value,'/[^\d]/','');
            if(empty($value)){
                unset($data[$key]);
            }
            unset($value);
        }



        $result = model('UserVideo')->updateInfo($data);
        return $result===true?json2(true,'操作成功',['data'=>$data,'share'=>$data['share']]):json2(false,'操作失败',['error'=>$result]);
    }

    // 添加视频
    public function addInfo_adasdasdasdasdadasdasdasd(){
        $data = [
            'title'=>request()->post('title/s',null,'strval'),
            'tag'=>request()->post('tag/a',[]),
            'category'=>request()->post('category/a',[]),
            'intro'=>request()->post('intro/s',null,'strval'),
            'is_private'=>request()->post('is_private/d',0,'intval'),
            'is_share'=>request()->post('is_share',0,'intval'),
            'share'=>request()->post('share/s',null,'strval'),
            'thumb'=>request()->post('thumb/s',null,'strval')
        ];

        foreach($data['tag'] as &$value){
            $value = replace($value,'/[^\x{4e00}-\x{9fa5}a-z0-9]/iu');
            unset($value);
        }

//        dd($data['category']);
        foreach($data['category'] as $key => &$value){
            $value = replace($value,'/[^\d]/','');
            if(empty($value)){
                unset($data[$key]);
            }
            unset($value);
        }



        $result = model('UserVideo')->addInfo($data);
        return $result===true?json2(true,'操作成功',['data'=>$data,'share'=>$data['share']]):json2(false,'操作失败',['error'=>$result]);
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

        $result = model('VideoCategory')->add($data);
        return $result===true?json2(true,'添加成功'):json2(false,'添加失败',['error'=>$result]);
    }

    public function category(){
        $category = toArray(VideoCategoryModel::all(['status'=>1]));
        $tree = new \tree($category);
        $result = $tree->create()->result();
        return json2(true,'',['result'=>$result]);
    }

    public function setStatus(){
        $id = request()->post('id/d',null,'intval');
        $status = request()->post('status/d',null,'intval');
        $status = array_key_exists('status',$_POST)?$_POST['status']:null;
        $status = $status?1:0;
        if(!$id || $status===null){
            return json2(false,'操作失败');
        }

        $model = UserVideoModel::get($id);
        // 超管无视帐号限制
        if(!$this->isSuper && $model->uid!==$this->uid){
            return json2(false,'操作失败');
        }

        if(empty($model)){
            return json2(false,'操作失败');
        }
        if($model->status===$status){
            return json2(true,'操作成功');
        }else{
            $model->save(['status'=>$status]);
            return json2(true,'操作成功');
        }

    }

    public function deleteVideo(){
        $id = request()->post('id/d',null,'intval');
        if(!$id){
            return json2(false,'操作失败');
        }

        $model = UserVideoModel::get($id);
        // 超管无视帐号限制
        if(!$this->isSuper && $model->uid!==$this->uid){
            return json2(false,'操作失败');
        }

        if(empty($model)){
            return json2(false,'操作失败');
        }
        $result = $model->delete();
        return $result?json2(true,'操作成功'):json2(false,'操作失败');

    }

    // 上传封面图
    public function uploadThumb(){
        $thumb = request()->file('thumb');
        if(empty($thumb)){
            return json2(false,'必须上传图片');
        }
        if(is_array($thumb)){
            return json2(false,'仅允许上传单个封面图');
        }
        $path = config('video.PATH');
//        最大2M
        $info = $thumb->validate(['size'=>2*1024*1024,'ext'=>'jpg,png'])->move($path.DS.'thumb');
        if($info){
            $link = lock('thumb'.DS.$info->getSaveName());
            $src = url('@link/Images/video_thumb',['link'=>$link],'');
            return json2(true,'上传成功',['thumb'=>$src]);
        }else{
            return json2(false,'上传失败',['error'=>$thumb->getError()]);
        }

//        $thumb
    }

    // 获取视频截图
    public function getScreenshoots(){
        $video_id = request()->post('video/s',null,'strval');
        if(empty($video_id)){
            return json2(true,'截图未生成',['finished'=>false]);
        }
        $path = config('video.PATH');
        $path = $path.DS.$video_id.DS.'screen';
        if(is_dir($path)){
            $files = scandir($path);
            array_shift($files);
            array_shift($files);
            if(count($files)>=5){

                $result = [];
                foreach($files as $key => $value){
                    $result[] = url('@link/Images/video_thumb',['link'=>lock($video_id.DS.'screen'.DS.$value)],'');
                }

                return json2(true,'截图已生成',['screenshoots'=>$result,'finished'=>true]);
            }else{
                return json2(true,'截图未生成',['finished'=>false]);
            }
        }else{
            return json2(true,'截图未生成',['finished'=>false]);
        }
    }

}