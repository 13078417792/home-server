<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/9
 * Time: 22:32
 */

namespace app\back\model;


class Access extends \think\Model{

    public static $type_list = ['api','views','extend'];

    public function accessRoleRelation(){
        return $this->hasMany('AccessRoleRelation','access_id','id');
    }

    public function addAccess(array $data){
        $validate = validate('Access');
        $valid = $validate->scene('add')->check($data);
        if($valid){
            $data['add_time'] = $_SERVER['REQUEST_TIME'];
            $data['update_time'] = $_SERVER['REQUEST_TIME'];
            $data['status'] = 1;
            $this->data($data)->allowField(true)->save();
            return true;
        }else{
            return $validate->getError();
        }
    }

    public function getAccessList(array $where=[]){
        $list = $this->where($where)->field('*,FROM_UNIXTIME(add_time) AS add_time_fmt')->order('type asc,id asc')->select();
        return $list;
    }

    public function getApiAccessList(){
        return $this->getAccessList(['type'=>'api']);
    }

    public function getViewsAccessList(){
        return $this->getAccessList(['type'=>'views']);
    }

    public function getExtendAccessList(){
        return $this->getAccessList(['type'=>'extend']);
    }

    public function deleteAccess($id){
        if(!$id){
            return '权限不存在';
        }
        $model = self::get($id);
        if(empty($model) || !$model->delete()){
            return '权限不存在';
        }
        $model->accessRoleRelation()->delete();
        return true;
    }

    public function getAccessDetail($id){
        if(!$id){
            return false;
        }
        $model = self::get(function($query)use($id){
            $query->where('id',$id)->field('*,FROM_UNIXTIME(add_time) AS add_time_fmt');
        });
        return empty($model)?false:toArray($model);
    }

    public function updateAccess(array $data){
        $validate = validate('Access');
        if($validate->scene('update')->check($data)){
            self::get($data['id'])->update($data);
            return true;
        }else{
            return $validate->getError();
        }
    }
}