<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/26
 * Time: 15:26
 */

namespace app\back\model;

use \app\back\controller\Video as VideoController;
class Video extends \think\Model{

    public $video_id = null;

    public function user(){
        return $this->hasMany('user_video','video_id','id');
    }

    // 添加视频
    public function add(array $data){
        $validate = validate('Video');
        if($validate->scene('add')->check($data)){

            return true;
        }else{
            return $validate->getError();
        }
    }


    public function upload(array $data,$token,$user_info){
        $uid = $user_info['id'];
        if(empty($data['file'])){
            return '没有文件被上传';
        }
        $file = $data['file'];
        if(!$file instanceof \think\File){
            return '非法的文件';
        }
        if((empty($data['index']) && $data['index']!=0) || empty($data['end'])){
            return '缺少必须数据';
        }
        $file_dir = config('video.PATH').DS.$data['md5'];
        $info = $file->validate(['ext'=>'mp4,rmvb,flv,mkv,avi']);
        if($info){
            if(!file_exists($file_dir.DS.$data['index'].'.'.$info->getExtension())){
                $info->move($file_dir,$data['index']);
            }
            if($data['end']===$data['index']){
                $model = self::get($data['md5']);
                if(empty($model)){
                    $this->data(['id'=>$data['md5'],'end'=>$data['end'],'time'=>$_SERVER['REQUEST_TIME'],'path'=>$file_dir])->save();
                    $relation = $this->user();
                }else{
                    $relation = $model->user();
                }

                $relation->save(['uid'=>$uid,'status'=>-1,'is_merge'=>0,'time'=>$_SERVER['REQUEST_TIME']]);

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

                    header('Content-Type:application/json;charset=utf-8');
                }
            }
            return true;
        }else{
            return $info->getError();
        }
    }
}