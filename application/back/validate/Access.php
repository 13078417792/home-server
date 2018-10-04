<?php
/**
 * Created by PhpStorm.
 * User: 39720
 * Date: 2018/9/17
 * Time: 17:03
 */

namespace app\back\validate;

use \app\back\model\Access as AccessModel;
class Access extends Base{

    protected $rule = [
        'id'=>'require|id_exist',
        'name'=>'require|length:2,20|unique2',
        'path'=>'require|length:3,255|unique2',
        'type'=>'require|type_exist'
    ];

    protected $message = [
        'id'=>'权限不存在',
        'name.require'=>'必须输入权限名称',
        'name.length'=>'权限名称长度为2-20',
        'name.unique'=>'权限名称已存在',
        'path.require'=>'必须输入路径',
        'path.length'=>'路径长度为3-255',
        'path.unique'=>'路径已存在',
        'type.require'=>'必须选择权限类型'
    ];

    protected $field = [
        'id'=>'权限',
        'name'=>'权限名称',
        'path'=>'权限路径',
        'type'=>'权限类型'
    ];

    protected $scene = [
        'add'=>['name','path','type'],
        'update'=>['id','name'=>'require|length:2,20|update_unique','path'=>'require|length:3,255|update_unique','type']
    ];

    protected function type_exist($value,$rule,$data){
        return in_array($value,AccessModel::$type_list)?true:'权限类型不正确';
    }

    protected function unique2($value,$rule,$data,$field,$desc){
        $exist = db('Access')->where([$field=>$value,'type'=>$data['type']])->find();
        return empty($exist)?true:$desc.'已存在';
    }

    protected function update_unique($value,$rule,$data,$field,$desc){
        $model = AccessModel::get(['id'=>['neq',$data['id']],$field=>$value,'type'=>$data['type']]);
        return empty($model)?true:$desc.'已存在';
    }

}