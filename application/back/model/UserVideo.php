<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/10/1
 * Time: 22:31
 */

namespace app\back\model;

use \app\link\controller\Images;
use \app\back\model\VideoTag;
use \app\back\model\VideoCategory;
class UserVideo extends \think\Model{

    public function video(){
        return $this->hasMany('Video','video_id','id');
    }

    public function user(){
        return $this->hasOne('Admin','uid','id');
    }

    public function video_list(array $condition){
        $where = [];
        if(!empty($condition['uid'])){
            $where['a.uid'] = $condition['uid'];
        }
        if(array_key_exists('is_private',$condition)){
            $where['a.is_private'] = $condition['is_private'];
        }
        if(array_key_exists('status',$condition)){
            $where['a.status'] = $condition['status'];
        }
        $result = $this->alias('a')
            ->join('admin b','a.uid=b.id')
            ->where($where)
            ->field('a.*,b.username,FROM_UNIXTIME(a.time) as time_str')
            ->order('a.id desc,a.uid asc')
            ->paginate(20);
        $result = page2Array($result,false);


        $category_model = model('VideoCategory');
        $category_list = $category_model->category(false); // 可用的视频分类列表

        $path = config('video.PATH');
        foreach($result['data'] as $key => &$value){
            $value['screenshoots'] = [];
            $screen_path = $path.DS.$value['video_id'].DS.'screen';
            if(file_exists($screen_path)){
                $list = scandir($screen_path);
                array_shift($list);array_shift($list);
                foreach($list as $v){
                    $value['screenshoots'][] = url('@link/images/video_thumb',['link'=>lock($value['video_id'].DS.'screen'.DS.$v)]);
                }


                $item_category_id_array = explode(',',$value['category']);
                $value['category_data'] = $category_model->getTreeSub($category_list,$item_category_id_array,['id','name','alias'=>'eng']);
            }
            unset($value);
        }



        return $result;
    }

    public function updateInfo(array $data){
//        dd($data);
        $validate = validate('UserVideo');
        $valid = $validate->scene('update')->check($data);

        if($valid){

            $tag = $data['tag'];
            $model = model('VideoTag');
            $tagIDArray = $model->saveTag($tag);
            $data['tag'] = implode(',',$tagIDArray);
//            dd($data);
            $data['is_private'] = $data['is_private']?1:0;
            if($data['is_share']){

                if(!empty($data['share'])){
//                    $data['share'] = replace($data['share'],'/[^a-z\d]/i');
                    if(preg_match_all('/[^a-z\d]/i',$data['share'])){
                        return '分享密码只能是大小写英文字符和数字';
                    }
                    $share_len = strlen($data['share']);
                    if($share_len>15 || $share_len<5){
                        return '分享密码的长度在5-15位之间';
                    }
                    $data['share'] = replace($data['share'],'/[^a-z\d]/i');
                }
            }else{
                $data['share'] = null;
            }
            unset($data['is_share']);
            $id = $data['id'];
            unset($data['id']);
            $data['time'] = $_SERVER['REQUEST_TIME'];
            $data['status'] = 1;

            if(!$data['is_private']){
                $data['share'] = null;
            }

            // 验证分类
            if(empty($data['category'])){
                return '必须选择分类';
            }
//            dd($data['category']);
            $category = VideoCategory::where(['id'=>['in',$data['category']],'status'=>1])->column('id,name');
//            dd($category);
            if(count($data['category'])!=count($category)){
                $diff = [];
                foreach($category as $key => $value){
                    if(in_array($key,$data['category'])){
                        $diff[] = $value;
                    }
                }
                $diff = implode(',',$diff);
                return $diff.'不存在';
            }
            $data['category'] = implode(',',$data['category']);

            $this->where('id',$id)->update($data);
//            dd($data);
            return true;
        }else{

            return $validate->getError();
        }
    }
}