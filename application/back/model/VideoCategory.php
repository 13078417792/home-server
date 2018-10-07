<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/10/6
 * Time: 20:31
 */

namespace app\back\model;


class VideoCategory extends \think\Model{

    static public $uniqidKey = [
        'name','pid','level','alias'
    ];

    static public function pid_exist($pid){
        $isArray = is_array($pid);
        if($isArray){
            $exist = self::all(['id'=>['in',$pid]]);
        }else{
            $exist = self::get($pid);
        }


        if(!$exist){
           return false;
        }
        $data = toArray($exist);
        if($isArray){
            $data = (new \tree($data))->create()->result();
            $data = (new \tree($data))->getDeepChildren()->result();
        }
        if($data['status']!=1){
            return false;
        }
        return $data;
    }

    public function add(array $data){
        $validate = validate('VideoCategory');
        $valid = $validate->scene('add')->check($data);
        if($valid){

            if(empty($data['pid'])){
                $data['pid'] = 0;
                $data['level'] = 1;
            }else{
                $parent = self::pid_exist($data['pid']);
                if($parent===false){
                    return '父分类不存在';
                }
                $data['level'] = $parent['level']+1;
//                $data['pid'] = $parent['id'];
            }


            $uniqid = [];
            foreach(self::$uniqidKey as $value){
                $uniqid[$value] = $data[$value];
            }
            $uniqid = substr(md5(serialize($uniqid)),8,16);
            $data['uniqid'] = $uniqid;
//            dd($uniqid);
            // uniqid是唯一的
            $exist = self::get(['uniqid'=>$uniqid]);
            if(!empty($exist)){
                return '分类名或别名已存在';
            }
            $exist = \think\Db::query("select * from video_category where pid=:pid and level=:level and (name=:name or alias=:alias)",['pid'=>$data['pid'],'level'=>$data['level'],'name'=>$data['name'],'alias'=>$data['alias']]);
            if(!empty($exist)){
                return '分类名或别名已存在';
            }
            $data['status'] = 1;


            $result = $this->data($data)->save();

            return $result?true:'添加失败';
        }else{
            return $validate->getError();
        }

    }

    public function category(){

    }
}